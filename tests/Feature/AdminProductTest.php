<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A valid 1x1 PNG; the GD extension is not available in this environment,
     * so UploadedFile::fake()->image() cannot be used.
     */
    private const string TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function fakeImage(string $name = 'produk.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::TINY_PNG));
    }

    public function test_admin_can_view_the_product_list(): void
    {
        $products = Product::factory()->count(3)->create();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk();
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_a_customer_cannot_open_the_product_list(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_when_opening_the_product_list(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_the_product_list_can_be_filtered_by_search_term(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = Product::factory()->create(['name' => 'Razer DeathAdder V3', 'brand' => 'Razer']);
        Product::factory()->create(['name' => 'Lenovo ThinkPad X1', 'brand' => 'Lenovo']);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['q' => 'deathadder']));

        $response->assertOk();
        $response->assertSee($matching->name);
        $response->assertDontSee('Lenovo ThinkPad X1');
    }

    public function test_the_product_list_can_be_filtered_by_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Mouse']);
        $inCategory = Product::factory()->forCategory($category)->create(['name' => 'Mouse Gaming']);
        Product::factory()->create(['name' => 'Laptop Kantor']);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['category' => $category->slug]));

        $response->assertOk();
        $response->assertSee($inCategory->name);
        $response->assertDontSee('Laptop Kantor');
    }

    public function test_the_product_list_can_be_filtered_by_brand(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = Product::factory()->create(['brand' => 'Keychron']);
        Product::factory()->create(['brand' => 'Acer']);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['brand' => 'Keychron']));

        $response->assertOk();
        $response->assertSee($matching->name);
    }

    public function test_the_product_list_can_be_filtered_by_stock_state(): void
    {
        $admin = User::factory()->admin()->create();
        $out = Product::factory()->outOfStock()->create(['name' => 'Produk Habis']);
        $low = Product::factory()->lowStock(3)->create(['name' => 'Produk Menipis']);
        Product::factory()->create(['stock' => 50, 'name' => 'Produk Aman']);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['stock' => 'out']));

        $response->assertOk();
        $response->assertSee($out->name);
        $response->assertDontSee('Produk Menipis');
        $response->assertDontSee('Produk Aman');

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['stock' => 'low']));
        $response->assertSee($low->name);
    }

    public function test_the_product_list_can_be_filtered_by_active_state(): void
    {
        $admin = User::factory()->admin()->create();
        $inactive = Product::factory()->inactive()->create(['name' => 'Produk Nonaktif']);
        Product::factory()->create(['name' => 'Produk Aktif']);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['status' => 'inactive']));

        $response->assertOk();
        $response->assertSee($inactive->name);
        $response->assertDontSee('Produk Aktif');
    }

    public function test_admin_can_create_a_product_with_specifications(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Keyboard Mekanik K87',
            'category_id' => $category->id,
            'brand' => 'Keychron',
            'price' => '1250000',
            'stock' => '10',
            'warranty_months' => '24',
            'is_active' => '1',
            'specs' => [
                ['key' => 'Switch', 'value' => 'Gateron Red'],
                ['key' => 'Layout', 'value' => '87 keys'],
                ['key' => 'Berat', 'value' => '850 gram'],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Keyboard Mekanik K87')->firstOrFail();
        $this->assertSame('keyboard-mekanik-k87', $product->slug);
        $this->assertSame([
            'Switch' => 'Gateron Red',
            'Layout' => '87 keys',
            'Berat' => '850 gram',
        ], $product->specs);
        $this->assertSame(1250000.0, (float) $product->price);
        $this->assertSame(10, $product->stock);
        $this->assertTrue($product->is_active);
    }

    public function test_product_specification_keys_must_be_distinct(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->from(route('admin.products.create'))
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Duplikat',
                'category_id' => $category->id,
                'brand' => 'Asus',
                'price' => '100000',
                'stock' => '1',
                'specs' => [
                    ['key' => 'Warna', 'value' => 'Hitam'],
                    ['key' => 'Warna', 'value' => 'Putih'],
                ],
            ]);

        $response->assertSessionHasErrors('specs.0.key');
        $this->assertDatabaseMissing('products', ['name' => 'Produk Duplikat']);
    }

    public function test_product_requires_a_known_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.products.create'))
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Tanpa Kategori',
                'category_id' => 999_999,
                'brand' => 'Asus',
                'price' => '100000',
                'stock' => '1',
            ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_product_price_must_not_be_negative(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->from(route('admin.products.create'))
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Murah',
                'category_id' => $category->id,
                'brand' => 'Asus',
                'price' => '-1000',
                'stock' => '1',
            ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_admin_can_upload_a_product_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Monitor Dengan Gambar',
            'category_id' => $category->id,
            'brand' => 'Dell',
            'price' => '3000000',
            'stock' => '4',
            'warranty_months' => '12',
            'image' => $this->fakeImage('monitor.png'),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Monitor Dengan Gambar')->firstOrFail();

        Storage::disk('public')->assertExists($product->image);
        $this->assertStringStartsWith('products/', $product->image);
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->from(route('admin.products.create'))
            ->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Lampiran',
                'category_id' => $category->id,
                'brand' => 'Asus',
                'price' => '100000',
                'stock' => '1',
                'image' => UploadedFile::fake()->createWithContent('dokumen.pdf', "%PDF-1.4\n%fake pdf for tests\n"),
            ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('products', ['name' => 'Produk Lampiran']);
    }

    public function test_admin_can_replace_and_remove_a_product_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Produk Ganti Gambar',
            'category_id' => $category->id,
            'brand' => 'LG',
            'price' => '900000',
            'stock' => '3',
            'warranty_months' => '12',
            'image' => $this->fakeImage('lama.png'),
        ]);

        $product = Product::query()->where('name', 'Produk Ganti Gambar')->firstOrFail();
        $originalImage = $product->image;
        Storage::disk('public')->assertExists($originalImage);

        // Replacing uploads a new file and removes the old one.
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Produk Ganti Gambar',
            'category_id' => $category->id,
            'brand' => 'LG',
            'price' => '950000',
            'stock' => '3',
            'warranty_months' => '12',
            'image' => $this->fakeImage('baru.png'),
        ]);

        $product->refresh();
        $this->assertNotSame($originalImage, $product->image);
        Storage::disk('public')->assertExists($product->image);
        Storage::disk('public')->assertMissing($originalImage);

        // Removing deletes the stored file and clears the column.
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Produk Ganti Gambar',
            'category_id' => $category->id,
            'brand' => 'LG',
            'price' => '950000',
            'stock' => '3',
            'warranty_months' => '12',
            'remove_image' => '1',
        ]);

        $product->refresh();
        $this->assertNull($product->image);
        Storage::disk('public')->assertMissing($product->getOriginal('image'));
    }

    public function test_admin_can_update_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Nama Awal',
            'price' => 500_000,
            'stock' => 5,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Nama Terbaru',
            'category_id' => $category->id,
            'brand' => $product->brand,
            'price' => '750000',
            'stock' => '12',
            'warranty_months' => '12',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('Nama Terbaru', $product->name);
        $this->assertSame('nama-terbaru', $product->slug);
        $this->assertSame(750000.0, (float) $product->price);
        $this->assertSame(12, $product->stock);
    }

    public function test_a_product_with_order_history_is_deactivated_instead_of_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        // Simulate order history without going through checkout.
        $order = Order::factory()->create();
        OrderItem::factory()->forProduct($product)->create(['order_id' => $order->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.products.index'))
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }

    public function test_a_product_without_order_history_is_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_toggle_product_visibility(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.products.status', $product));

        $this->assertFalse($product->refresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.products.status', $product));

        $this->assertTrue($product->refresh()->is_active);
    }

    public function test_a_customer_cannot_create_products(): void
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($customer)->post(route('admin.products.store'), [
            'name' => 'Produk Penyusup',
            'category_id' => $category->id,
            'brand' => 'Asus',
            'price' => '1000',
            'stock' => '1',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('products', ['name' => 'Produk Penyusup']);
    }
}
