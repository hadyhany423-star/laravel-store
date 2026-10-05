<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProductImageSeeder extends Seeder
{
    private float $lastRequestAt = 0;

    public function run(): void
    {
        $downloaded = 0;
        $skipped = 0;
        $failed = 0;
        $usedImageHashes = [];
        $usedSourceUrls = [];

        Product::query()->whereNotNull('image')->orderBy('id')->chunkById(50, function ($products) use (&$usedImageHashes): void {
            foreach ($products as $product) {
                if (Storage::disk('public')->exists($product->image)) {
                    $usedImageHashes[hash('sha256', Storage::disk('public')->get($product->image))] = true;
                }
            }
        });

        Product::query()->with('category')->orderBy('id')->chunkById(50, function ($products) use (&$downloaded, &$skipped, &$failed, &$usedImageHashes, &$usedSourceUrls): void {
            foreach ($products as $product) {
                $managedImage = preg_match('/^products\/(?:product-\d+|matched-product-\d+)\.[a-z0-9]+$/i', (string) $product->image) === 1;
                $matchedImage = str_starts_with((string) $product->image, "products/matched-product-{$product->id}.");
                $hasLocalImage = $product->image && Storage::disk('public')->exists($product->image);
                $creditPath = "products/matched-product-{$product->id}-credits.txt";

                if ($hasLocalImage && !$managedImage) {
                    $skipped++;
                    continue;
                }

                if ($matchedImage && $hasLocalImage && Storage::disk('public')->exists($creditPath)) {
                    $skipped++;
                    continue;
                }

                try {
                    [$imageContents, $extension, $imageHash, $sourceUrl, $credit] = $this->downloadMatchingImage(
                        $product,
                        $usedImageHashes,
                        $usedSourceUrls
                    );

                    $path = "products/matched-product-{$product->id}.{$extension}";
                    if (!Storage::disk('public')->put($path, $imageContents)) {
                        throw new RuntimeException('The image could not be saved to the public disk.');
                    }

                    $previousPath = $product->image;
                    if (!Storage::disk('public')->put($creditPath, $credit)) {
                        Storage::disk('public')->delete($path);
                        throw new RuntimeException('Image attribution could not be saved.');
                    }

                    $product->forceFill(['image' => $path])->save();
                    if ($managedImage && $previousPath !== $path && Storage::disk('public')->exists($previousPath)) {
                        Storage::disk('public')->delete($previousPath);
                    }
                    $usedImageHashes[$imageHash] = true;
                    $usedSourceUrls[$sourceUrl] = true;
                    $downloaded++;
                } catch (Throwable $exception) {
                    $failed++;
                    Log::warning('Product image download failed.', [
                        'product_id' => $product->id,
                        'error' => $exception->getMessage(),
                    ]);
                    $this->command?->warn("Image download failed for product {$product->id}.");
                }
            }
        });

        $this->command?->info("Product images downloaded: {$downloaded}; already present: {$skipped}; failed: {$failed}.");
    }

    private function downloadMatchingImage(Product $product, array &$usedImageHashes, array &$usedSourceUrls): array
    {
        $lastFailure = null;
        $userAgent = 'StorePortfolioProductImageSeeder/1.0 ('.config('app.url').')';

        foreach ($this->imageKeywords($product) as $keywords) {
            foreach (["filetype:bitmap \"{$keywords}\"", "filetype:bitmap {$keywords}"] as $searchQuery) {
                try {
                    $searchResponse = $this->requestWikimedia(
                        'https://commons.wikimedia.org/w/api.php',
                        [
                            'action' => 'query',
                            'generator' => 'search',
                            'gsrsearch' => $searchQuery,
                            'gsrnamespace' => 6,
                            'gsrlimit' => 20,
                            'prop' => 'imageinfo',
                            'iiprop' => 'url|mime|extmetadata',
                            'iiurlwidth' => 900,
                            'format' => 'json',
                        ],
                        $userAgent
                    );

                    $pages = array_values($searchResponse->json('query.pages', []));
                    usort($pages, fn (array $left, array $right): int => ($left['index'] ?? PHP_INT_MAX) <=> ($right['index'] ?? PHP_INT_MAX));

                    foreach ($pages as $page) {
                        $imageInfo = $page['imageinfo'][0] ?? [];
                        $sourceUrl = $imageInfo['thumburl'] ?? $imageInfo['url'] ?? null;
                        if (!str_starts_with($imageInfo['mime'] ?? '', 'image/') || !$sourceUrl || isset($usedSourceUrls[$sourceUrl])) {
                            continue;
                        }

                        $imageResponse = $this->requestWikimedia($sourceUrl, [], $userAgent, true);

                        $imageContents = $imageResponse->body();
                        $imageMetadata = @getimagesizefromstring($imageContents);
                        $extension = match ($imageMetadata['mime'] ?? null) {
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                            default => null,
                        };

                        if (!$extension || strlen($imageContents) > 10 * 1024 * 1024) {
                            continue;
                        }

                        $imageHash = hash('sha256', $imageContents);
                        if (isset($usedImageHashes[$imageHash])) {
                            $usedSourceUrls[$sourceUrl] = true;
                            continue;
                        }

                        return [
                            $imageContents,
                            $extension,
                            $imageHash,
                            $sourceUrl,
                            $this->formatAttribution($imageInfo, $imageHash),
                        ];
                    }

                    $lastFailure = new RuntimeException("No unused image found for '{$keywords}'.");
                } catch (Throwable $exception) {
                    $lastFailure = $exception;
                }
            }
        }

        throw $lastFailure ?? new RuntimeException('No matching image was returned.');
    }

    private function requestWikimedia(string $url, array $query, string $userAgent, bool $image = false): Response
    {
        $response = null;

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $elapsed = microtime(true) - $this->lastRequestAt;
            if ($elapsed < 6) {
                usleep((int) ((6 - $elapsed) * 1_000_000));
            }
            $this->lastRequestAt = microtime(true);

            $request = Http::withUserAgent($userAgent)->timeout(30);
            $request = $image ? $request->accept('image/*') : $request->acceptJson();
            $response = $request->get($url, $query);

            if (!in_array($response->status(), [429, 503], true)) {
                return $response->throw();
            }

            if ($attempt < 3) {
                $retryAfter = filter_var($response->header('Retry-After'), FILTER_VALIDATE_INT);
                sleep(max(6, min(120, $retryAfter ?: (30 * ($attempt + 1)))));
            }
        }

        if (!$response) {
            throw new RuntimeException('Wikimedia did not return a response.');
        }

        return $response->throw();
    }

    private function imageKeywords(Product $product): array
    {
        $name = Str::lower($product->name);
        $specificKeywords = match (true) {
            Str::contains($name, ['طقم أواني', 'أواني طهي', 'cookware']) => 'kitchen cookware',
            Str::contains($name, ['مكواة', 'steam iron', 'iron']) => 'steam iron',
            Str::contains($name, ['ساعة ذكية', 'smart watch', 'smartwatch']) => 'smart watch',
            Str::contains($name, ['ساعة', 'watch']) => 'wristwatch',
            Str::contains($name, ['سماعات', 'سماعة', 'headphones', 'earbuds']) => 'wireless headphones',
            Str::contains($name, ['شاحن', 'charger']) => 'mobile phone charger',
            Str::contains($name, ['لوحة مفاتيح', 'keyboard']) => 'computer keyboard',
            Str::contains($name, ['حامل هاتف', 'phone stand', 'phone holder']) => 'phone holder',
            Str::contains($name, ['ماوس', 'mouse']) => 'computer mouse',
            Str::contains($name, ['باور بانك', 'power bank']) => 'power bank',
            Str::contains($name, ['شاشة', 'monitor']) => 'computer monitor',
            Str::contains($name, ['لابتوب', 'laptop']) => 'laptop computer',
            Str::contains($name, ['هاتف', 'smartphone', 'mobile phone']) => 'smartphone',
            Str::contains($name, ['تيشيرت', 't-shirt', 'tee shirt']) => 'cotton t-shirt',
            Str::contains($name, ['حقيبة', 'handbag', 'bag']) => 'handbag',
            Str::contains($name, ['وشاح', 'scarf']) => 'fashion scarf',
            Str::contains($name, ['قميص', 'shirt']) => 'casual shirt',
            Str::contains($name, ['محفظة', 'wallet']) => 'leather wallet',
            Str::contains($name, ['جينز', 'jeans']) => 'blue jeans',
            Str::contains($name, ['حذاء', 'sneakers', 'shoes']) => 'sneakers',
            Str::contains($name, ['فستان', 'dress']) => 'fashion dress',
            Str::contains($name, ['جاكيت', 'jacket']) => 'fashion jacket',
            Str::contains($name, ['نظارة', 'sunglasses']) => 'sunglasses',
            Str::contains($name, ['خلاط', 'blender']) => 'kitchen blender',
            Str::contains($name, ['مصباح', 'lamp']) => 'desk lamp',
            Str::contains($name, ['منظم', 'organizer']) => 'kitchen organizer',
            Str::contains($name, ['غلاية', 'kettle']) => 'electric kettle',
            Str::contains($name, ['مروحة', 'fan']) => 'electric fan',
            Str::contains($name, ['قهوة', 'coffee maker']) => 'coffee maker',
            Str::contains($name, ['قلاية', 'air fryer']) => 'air fryer',
            Str::contains($name, ['مكنسة', 'vacuum']) => 'vacuum cleaner',
            Str::contains($name, ['معطر', 'fragrance diffuser']) => 'home fragrance diffuser',
            Str::contains($name, ['واقي شمس', 'sunscreen']) => 'sunscreen lotion',
            Str::contains($name, ['سيروم', 'serum']) => 'face serum skincare',
            Str::contains($name, ['غسول', 'cleanser']) => 'facial cleanser',
            Str::contains($name, ['مرطب', 'moisturizer']) => 'face moisturizer',
            Str::contains($name, ['عطر', 'perfume']) => 'perfume bottle',
            Str::contains($name, ['مكياج', 'makeup']) => 'makeup cosmetics',
            Str::contains($name, ['شامبو', 'shampoo']) => 'shampoo bottle',
            Str::contains($name, ['عناية بالبشرة', 'skincare']) => 'skincare products',
            default => null,
        };

        $categoryKeywords = match ($product->category?->slug) {
            'electronics' => 'consumer electronics',
            'clothing', 'mlabs' => 'fashion clothing',
            'home-appliances' => 'home kitchen appliances',
            'beauty' => 'beauty skincare cosmetics',
            default => null,
        };

        if (!$specificKeywords && preg_match('/[a-z]/i', $product->name)) {
            $specificKeywords = Str::slug($product->name, ' ');
        }

        return array_values(array_unique(array_filter([$specificKeywords, $categoryKeywords])));
    }

    private function formatAttribution(array $imageInfo, string $imageHash): string
    {
        $metadata = $imageInfo['extmetadata'] ?? [];
        $value = function (string $key) use ($metadata): string {
            $text = strip_tags($metadata[$key]['value'] ?? '');

            return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        };

        return implode(PHP_EOL, array_filter([
            'Source: '.($imageInfo['descriptionurl'] ?? $imageInfo['url'] ?? 'Wikimedia Commons'),
            'Creator: '.$value('Artist'),
            'License: '.$value('LicenseShortName'),
            'License URL: '.$value('LicenseUrl'),
            'SHA-256: '.$imageHash,
        ]));
    }
}
