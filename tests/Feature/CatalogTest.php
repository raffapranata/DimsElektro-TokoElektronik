<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_renders_featured_products(): void
    {
        $product = Product::factory()->create(['name' => 'Laptop Andalan']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Laptop Andalan')
            ->assertSee('Peralatan kerja');
    }

    public function test_home_page_hides_products_that_are_out_of_stock(): void
    {
        Product::factory()->outOfStock()->create(['name' => 'Produk Kosong']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Kosong');
    }

    public function test_catalog_lists_active_products_only(): void
    {
        Product::factory()->create(['name' => 'Produk Tampil']);
        Product::factory()->inactive()->create(['name' => 'Produk Arsip']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Produk Tampil')
            ->assertDontSee('Produk Arsip');
    }

    public function test_catalog_can_be_searched_by_name(): void
    {
        Product::factory()->create(['name' => 'Monitor Ultrawide 34']);
        Product::factory()->create(['name' => 'Mouse Wireless']);

        $response = $this->get(route('products.index', ['q' => 'Ultrawide']));

        $response->assertOk();
        $response->assertSee('Monitor Ultrawide 34');
        $response->assertDontSee('Mouse Wireless');
        $response->assertSee('1 produk ditemukan');
    }

    public function test_catalog_can_be_searched_by_brand(): void
    {
        Product::factory()->create(['name' => 'Laptop Kerja', 'brand' => 'Lenovo']);
        Product::factory()->create(['name' => 'Laptop Gaming', 'brand' => 'Asus']);

        $response = $this->get(route('products.index', ['q' => 'Lenovo']));

        $response->assertOk();
        $response->assertSee('Laptop Kerja');
        $response->assertDontSee('Laptop Gaming');
    }

    public function test_search_wildcards_are_treated_as_literal_characters(): void
    {
        Product::factory()->create(['name' => 'Mouse Biasa']);
        Product::factory()->create(['name' => 'Diskon 100% Keyboard']);

        $response = $this->get(route('products.index', ['q' => '%']));

        $response->assertOk();
        $response->assertSee('Diskon 100% Keyboard');
        $response->assertDontSee('Mouse Biasa');
    }

    public function test_catalog_can_be_filtered_by_category(): void
    {
        $keyboard = Category::factory()->create(['name' => 'Keyboard']);
        $mouse = Category::factory()->create(['name' => 'Mouse']);

        Product::factory()->forCategory($keyboard)->create(['name' => 'Keyboard Mekanikal']);
        Product::factory()->forCategory($mouse)->create(['name' => 'Mouse Gaming']);

        $response = $this->get(route('products.index', ['category' => $mouse->slug]));

        $response->assertOk();
        $response->assertSee('Mouse Gaming');
        $response->assertDontSee('Keyboard Mekanikal');
    }

    public function test_catalog_can_be_filtered_by_brand(): void
    {
        Product::factory()->create(['name' => 'Produk Samsung', 'brand' => 'Samsung']);
        Product::factory()->create(['name' => 'Produk Epson', 'brand' => 'Epson']);

        $response = $this->get(route('products.index', ['brand' => 'Samsung']));

        $response->assertOk();
        $response->assertSee('Produk Samsung');
        $response->assertDontSee('Produk Epson');
    }

    public function test_catalog_sorts_by_price_when_requested(): void
    {
        Product::factory()->create(['name' => 'Termurah', 'price' => 100_000]);
        Product::factory()->create(['name' => 'Termahal', 'price' => 9_000_000]);

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));

        $response->assertOk();
        $this->assertGreaterThan(
            strpos($response->getContent(), 'Termurah'),
            strpos($response->getContent(), 'Termahal'),
        );
    }

    public function test_catalog_ignores_an_unknown_sort_value(): void
    {
        Product::factory()->create(['name' => 'Produk Aman']);

        $this->get(route('products.index', ['sort' => 'harga; DROP TABLE products']))
            ->assertOk()
            ->assertSee('Produk Aman');
    }

    public function test_catalog_paginates_the_result_set(): void
    {
        Product::factory()->count(12)->create();

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $this->assertSame(9, $response->viewData('products')->count());
        $this->assertSame(12, $response->viewData('products')->total());
    }

    public function test_product_detail_renders_specifications(): void
    {
        $product = Product::factory()->create([
            'name' => 'Asus Zenbook 14',
            'specs' => ['Processor' => 'Intel Core i5-1335U', 'RAM' => '16 GB'],
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Asus Zenbook 14')
            ->assertSee('Intel Core i5-1335U')
            ->assertSee('16 GB');
    }

    public function test_product_detail_reports_out_of_stock_and_hides_the_cart_form(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('Stok habis');
        // No quantity field means no add-to-cart form was rendered.
        $response->assertDontSee('name="quantity"', false);
    }

    public function test_inactive_product_detail_is_not_reachable(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get(route('products.show', $product))->assertNotFound();
    }
}
