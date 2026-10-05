<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            'إلكترونيات' => 'electronics',
            'ملابس وأزياء' => 'clothing',
            'أجهزة منزلية' => 'home-appliances',
            'عطور ومساحيق تجميل' => 'beauty',
        ];

        foreach ($categoriesData as $name => $slug) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $categoryTemplates = [
            'إلكترونيات' => [
                ['name' => 'سماعات بلوتوث لاسلكية', 'description' => 'صوت واضح وبطارية تدوم طوال اليوم.', 'min' => 500, 'max' => 3500],
                ['name' => 'ساعة ذكية متعددة الاستخدامات', 'description' => 'شاشة عملية لمتابعة النشاط والتنبيهات.', 'min' => 1200, 'max' => 7000],
                ['name' => 'شاحن سريع متعدد المنافذ', 'description' => 'شحن آمن وسريع للأجهزة اليومية.', 'min' => 300, 'max' => 1800],
                ['name' => 'لوحة مفاتيح لاسلكية', 'description' => 'كتابة مريحة واتصال ثابت للاستخدام المنزلي.', 'min' => 500, 'max' => 2600],
                ['name' => 'حامل هاتف قابل للتعديل', 'description' => 'حامل ثابت مناسب للمكتب ومشاهدة المحتوى.', 'min' => 200, 'max' => 800],
                ['name' => 'ماوس لاسلكي مريح', 'description' => 'استجابة دقيقة وتصميم مريح للعمل اليومي.', 'min' => 300, 'max' => 1300],
                ['name' => 'باور بانك سريع 20000 مللي أمبير', 'description' => 'سعة كبيرة لشحن الهاتف أثناء التنقل.', 'min' => 900, 'max' => 2400],
                ['name' => 'شاشة كمبيوتر عالية الدقة', 'description' => 'شاشة واضحة مناسبة للعمل والترفيه.', 'min' => 4500, 'max' => 12000],
                ['name' => 'سماعة أذن بعزل الضوضاء', 'description' => 'استماع مريح مع عزل فعال للضوضاء المحيطة.', 'min' => 1400, 'max' => 5200],
                ['name' => 'هاتف ذكي بسعة تخزين كبيرة', 'description' => 'أداء يومي سريع وكاميرا واضحة ومساحة واسعة.', 'min' => 9000, 'max' => 28000],
            ],
            'ملابس وأزياء' => [
                ['name' => 'تيشيرت قطني يومي', 'description' => 'قطن ناعم بقصة مريحة للاستخدام اليومي.', 'min' => 250, 'max' => 900],
                ['name' => 'حقيبة كتف عملية', 'description' => 'تصميم خفيف ومساحة مناسبة للاحتياجات اليومية.', 'min' => 500, 'max' => 2200],
                ['name' => 'وشاح بألوان موسمية', 'description' => 'خامة ناعمة تضيف لمسة بسيطة للإطلالة.', 'min' => 200, 'max' => 800],
                ['name' => 'قميص كاجوال خفيف', 'description' => 'قميص عملي بقصة مريحة للمشاوير اليومية.', 'min' => 450, 'max' => 1500],
                ['name' => 'محفظة صغيرة متعددة الجيوب', 'description' => 'تنظيم بسيط للمقتنيات الأساسية بحجم مناسب.', 'min' => 250, 'max' => 1000],
                ['name' => 'جينز بقصة مستقيمة', 'description' => 'خامة متينة وقصة عملية تناسب الإطلالات اليومية.', 'min' => 700, 'max' => 1900],
                ['name' => 'حذاء رياضي خفيف', 'description' => 'نعل مريح وخامة مناسبة للمشي والاستخدام اليومي.', 'min' => 900, 'max' => 2800],
                ['name' => 'فستان يومي بتصميم بسيط', 'description' => 'قماش مريح وتصميم أنيق للمشاوير والمناسبات.', 'min' => 850, 'max' => 2400],
                ['name' => 'جاكيت خفيف متعدد الاستخدام', 'description' => 'طبقة عملية مناسبة للأجواء المعتدلة.', 'min' => 1100, 'max' => 3200],
                ['name' => 'نظارة شمسية بإطار أنيق', 'description' => 'إطار خفيف وعدسات مناسبة للاستخدام اليومي.', 'min' => 500, 'max' => 1800],
            ],
            'أجهزة منزلية' => [
                ['name' => 'خلاط كهربائي عملي', 'description' => 'إعداد سريع للعصائر والمكونات اليومية.', 'min' => 1000, 'max' => 4500],
                ['name' => 'مصباح مكتبي موفر للطاقة', 'description' => 'إضاءة مريحة مع تصميم مناسب للمكتب والمنزل.', 'min' => 400, 'max' => 1600],
                ['name' => 'منظم أدوات للمطبخ', 'description' => 'يساعد على ترتيب الأدوات واستغلال المساحة.', 'min' => 200, 'max' => 1000],
                ['name' => 'غلاية مياه سريعة', 'description' => 'تسخين عملي للمشروبات مع إيقاف تلقائي.', 'min' => 600, 'max' => 2200],
                ['name' => 'مروحة مكتب هادئة', 'description' => 'حجم صغير وتدفق هواء مناسب للمساحات المحدودة.', 'min' => 450, 'max' => 1700],
                ['name' => 'ماكينة تحضير قهوة منزلية', 'description' => 'تحضير سهل للقهوة في المنزل بتصميم مدمج.', 'min' => 1800, 'max' => 6500],
                ['name' => 'قلاية هوائية عائلية', 'description' => 'سعة مناسبة للطهي اليومي مع استهلاك زيت أقل.', 'min' => 2800, 'max' => 8500],
                ['name' => 'مكواة بخار بخزان كبير', 'description' => 'كي سريع ونتائج مرتبة لمختلف أنواع الأقمشة.', 'min' => 1300, 'max' => 4200],
                ['name' => 'مكنسة كهربائية قوية', 'description' => 'تنظيف فعال للأرضيات والسجاد مع سهولة الحركة.', 'min' => 2500, 'max' => 9000],
                ['name' => 'طقم أواني طهي غير لاصق', 'description' => 'طقم عملي للاستخدام اليومي وسهل التنظيف.', 'min' => 1600, 'max' => 5200],
            ],
            'عطور ومساحيق تجميل' => [
                ['name' => 'عطر  برائحة منعشة', 'description' => 'رائحة متوازنة مناسبة للاستخدام اليومي.', 'min' => 450, 'max' => 2600],
                ['name' => 'مجموعة عناية بالبشرة', 'description' => 'أساسيات لطيفة للعناية والترطيب اليومي.', 'min' => 400, 'max' => 2200],
                ['name' => 'كريم مرطب ', 'description' => 'ترطيب سريع بملمس خفيف على البشرة.', 'min' => 200, 'max' => 1000],
                ['name' => 'غسول وجه ', 'description' => 'تنظيف يومي لطيف يناسب روتين العناية البسيط.', 'min' => 180, 'max' => 800],
                ['name' => 'معطر منزلي ', 'description' => 'رائحة هادئة تضيف انتعاشًا للمكان.', 'min' => 250, 'max' => 1200],
                ['name' => 'واقي شمس بعامل حماية مرتفع', 'description' => 'تركيبة خفيفة للحماية اليومية من أشعة الشمس.', 'min' => 350, 'max' => 1200],
                ['name' => 'سيروم ترطيب للوجه', 'description' => 'ترطيب مركز ضمن روتين العناية اليومي.', 'min' => 500, 'max' => 1800],
                ['name' => 'عطر شرقي للمناسبات', 'description' => 'تركيبة دافئة وثبات مناسب للمناسبات.', 'min' => 800, 'max' => 3500],
                ['name' => 'مجموعة مستحضرات مكياج أساسية', 'description' => 'مجموعة مختارة لإطلالة يومية متكاملة.', 'min' => 650, 'max' => 2400],
                ['name' => 'شامبو للعناية بالشعر', 'description' => 'تنظيف لطيف يساعد على الحفاظ على نعومة الشعر.', 'min' => 220, 'max' => 900],
            ],
        ];

        $genericTemplates = [
            ['name' => 'قطعة أساسية مختارة', 'description' => 'منتج عملي مختار بعناية للاستخدام اليومي.', 'min' => 300, 'max' => 2500],
            ['name' => 'اختيار مميز', 'description' => 'تصميم موثوق يجمع بين الجودة وسهولة الاستخدام.', 'min' => 400, 'max' => 3200],
            ['name' => 'منتج جديد للموسم', 'description' => 'إضافة جديدة تناسب احتياجات هذا القسم.', 'min' => 200, 'max' => 1800],
            ['name' => 'إضافة عملية للمنزل', 'description' => 'اختيار بسيط يضيف قيمة للاستخدام اليومي.', 'min' => 300, 'max' => 2800],
            ['name' => 'منتج مختار بعناية', 'description' => 'جودة مناسبة وسعر متوازن ضمن هذا القسم.', 'min' => 200, 'max' => 2200],
            ['name' => 'إكسسوار عملي', 'description' => 'إضافة مفيدة بتصميم بسيط وجودة مناسبة.', 'min' => 250, 'max' => 1800],
            ['name' => 'طقم استخدام يومي', 'description' => 'مجموعة عملية تناسب احتياجاتك اليومية.', 'min' => 500, 'max' => 3500],
            ['name' => 'إصدار جديد', 'description' => 'تصميم حديث بخامات مختارة بعناية.', 'min' => 400, 'max' => 3000],
            ['name' => 'منتج متعدد الاستخدامات', 'description' => 'حل عملي يجمع أكثر من ميزة في منتج واحد.', 'min' => 350, 'max' => 2800],
            ['name' => 'اختيار اقتصادي موثوق', 'description' => 'قيمة جيدة وجودة مناسبة للاستخدام المتكرر.', 'min' => 200, 'max' => 1500],
        ];

        foreach (Category::query()->get() as $category) {
            $existingNames = $category->products()->pluck('name')->all();
            $missingCount = max(0, 10 - count($existingNames));
            if ($missingCount === 0) {
                continue;
            }

            $templateKey = match ($category->slug) {
                'electronics' => 'إلكترونيات',
                'clothing', 'mlabs' => 'ملابس وأزياء',
                'home-appliances' => 'أجهزة منزلية',
                'beauty' => 'عطور ومساحيق تجميل',
                default => $category->name,
            };
            $templates = $categoryTemplates[$templateKey] ?? $genericTemplates;
            $templates = array_values(array_filter(
                $templates,
                fn (array $template): bool => !in_array($template['name'], $existingNames, true)
            ));
            shuffle($templates);

            foreach (array_slice($templates, 0, $missingCount) as $template) {
                $name = $template['name'];
                $slug = (Str::slug($name) ?: 'product').'-'.Str::lower(Str::random(8));
                $priceMin = (int) ceil(($template['min'] + 1) / 100);
                $priceMax = (int) floor(($template['max'] + 1) / 100);

                Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $template['description'].' مناسب لقسم '.$category->name.'.',
                    'price' => random_int($priceMin, $priceMax) * 100 - 1,
                    'stock' => random_int(5, 60),
                ]);
            }
        }
    }
}
