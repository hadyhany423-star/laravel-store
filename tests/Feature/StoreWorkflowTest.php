<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_create_a_seller(): void
    {
        $this->post('/register', [
            'name' => 'New account',
            'email_or_phone' => 'buyer@example.com',
            'password' => 'password123',
            'role' => 'seller',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);
    }

    public function test_product_management_is_restricted_to_sellers(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->get('/products/create')->assertRedirect('/login');
        $this->actingAs($buyer)->get('/products/create')->assertForbidden();
    }

    public function test_checkout_uses_database_price_saves_address_and_reduces_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 5, price: '10.00');

        $this->actingAs($user)
            ->withSession(['cart' => [
                $product->id => ['name' => $product->name, 'price' => 0.01, 'quantity' => 2],
            ]])
            ->post('/checkout', ['address' => '12 Main Street'])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => '20.00',
            'status' => 'pending',
            'address' => '12 Main Street',
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => '10.00',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_checkout_rejects_quantities_above_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 1);

        $this->actingAs($user)
            ->withSession(['cart' => [
                $product->id => ['name' => $product->name, 'price' => $product->price, 'quantity' => 2],
            ]])
            ->post('/checkout', ['address' => '12 Main Street'])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    public function test_cart_add_update_and_remove_adjust_reserved_stock(): void
    {
        $product = $this->createProduct(stock: 5);

        $this->post('/cart/add/'.$product->id)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);

        $this->post('/cart/add/'.$product->id)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);

        $this->post('/cart/update/'.$product->id, ['quantity' => 1])->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);

        $this->post('/cart/remove/'.$product->id)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);

        $this->post('/cart/add/'.$product->id)->assertRedirect();
        $this->post('/cart/add/'.$product->id)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);

        $this->post('/cart/clear')->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
    }

    public function test_checkout_does_not_deduct_reserved_stock_twice(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 5);

        $this->actingAs($user)
            ->post('/cart/add/'.$product->id)
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);

        $this->post('/checkout', ['address' => '12 Main Street'])->assertRedirect('/profile');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_cancelling_an_order_restores_stock_and_keeps_order_history(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 3);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 10,
            'status' => 'pending',
            'address' => '12 Main Street',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10,
        ]);
        $product->decrement('stock', 2);

        $this->actingAs($user)->post('/orders/'.$order->id.'/cancel')->assertRedirect('/profile');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_seller_can_edit_a_category_and_slug_collisions_are_resolved(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::create(['name' => 'Old category', 'slug' => 'old-category']);
        Category::create(['name' => 'New category', 'slug' => 'new-category']);

        $this->actingAs($seller)
            ->get('/categories/'.$category->id.'/edit')
            ->assertOk()
            ->assertSee('Old category');

        $this->post('/categories/'.$category->id.'/update', ['name' => 'New category!'])
            ->assertRedirect('/categories/create');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New category!',
            'slug' => 'new-category-2',
        ]);
    }

    public function test_seller_can_replace_a_product_image_without_editing_other_fields(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct(stock: 4);
        $originalName = $product->name;
        $originalPrice = $product->price;
        Storage::disk('public')->put('products/old-image.jpg', 'old image');
        $product->update(['image' => 'products/old-image.jpg']);

        $this->actingAs($seller)
            ->get('/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertSee('تعديل الصور')
            ->assertSee('/products/'.$product->id.'/images/edit');

        $this->actingAs($seller)
            ->get('/products/'.$product->id.'/images/edit')
            ->assertOk()
            ->assertSee('تعديل صور المنتج');

        $this->actingAs($seller)
            ->post('/products/'.$product->id.'/images', [
                'image' => UploadedFile::fake()->create('replacement.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect('/products');

        $product->refresh();
    $this->assertSame($originalName, $product->name);
        $this->assertNotSame('products/old-image.jpg', $product->image);
        $this->assertEquals($originalPrice, $product->price);
        $this->assertFalse(Storage::disk('public')->exists('products/old-image.jpg'));
        $this->assertTrue(Storage::disk('public')->exists($product->image));
    }

    public function test_store_seeder_preserves_existing_products_and_is_idempotent(): void
    {
        $category = Category::create(['name' => 'User category', 'slug' => 'user-category']);
        $existingProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Existing product',
            'slug' => 'existing-product',
            'price' => 25,
            'stock' => 4,
        ]);

        $seeder = app(StoreSeeder::class);
        $seeder->run();

        $this->assertDatabaseHas('products', ['id' => $existingProduct->id]);
        $this->assertGreaterThanOrEqual(10, Category::withCount('products')->get()->min('products_count'));
        $countsAfterFirstRun = Category::withCount('products')->orderBy('id')->pluck('products_count')->all();

        $seeder->run();

        $this->assertSame(
            $countsAfterFirstRun,
            Category::withCount('products')->orderBy('id')->pluck('products_count')->all()
        );
    }

    private function createProduct(int $stock, string $price = '10.00'): Product
    {
        $category = Category::create(['name' => 'General', 'slug' => 'general']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Test product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'price' => $price,
            'stock' => $stock,
        ]);
    }
}
