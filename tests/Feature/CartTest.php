<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_empty_cart_renders_an_empty_state(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Keranjang Anda masih kosong');
    }

    public function test_guest_can_add_a_product_to_the_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 1_500_000]);

        $response = $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('cart', [$product->id => 2]);

        $this->get(route('cart.index'))->assertSee($product->name);
    }

    public function test_adding_the_same_product_twice_accumulates_the_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->get(route('cart.index'))->assertSessionHas('cart', [$product->id => 4]);
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $response = $this->from(route('products.index'))->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('quantity');
        $response->assertSessionMissing('cart');
    }

    public function test_accumulated_quantity_is_capped_at_available_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);
        // 2 + 2 exceeds the stock of 3, so the line is capped rather than lost.
        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->get(route('cart.index'))->assertSessionHas('cart', [$product->id => 3]);
    }

    public function test_a_product_that_is_out_of_stock_cannot_be_added(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $response = $this->from(route('products.index'))->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasErrors('quantity');
        $response->assertSessionMissing('cart');
    }

    public function test_an_inactive_product_cannot_be_added(): void
    {
        $product = Product::factory()->inactive()->create();

        $response = $this->from(route('products.index'))->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_quantity_must_be_at_least_one(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->from(route('products.index'))->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_an_unknown_product_cannot_be_added(): void
    {
        $response = $this->from(route('products.index'))->post(route('cart.store'), [
            'product_id' => 999_999,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_cart_line_quantity_can_be_updated(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->withSession(['cart' => [$product->id => 1]]);

        $response = $this->patch(route('cart.update', $product), ['quantity' => 5]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('cart', [$product->id => 5]);
    }

    public function test_updating_beyond_available_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->withSession(['cart' => [$product->id => 1]]);

        $response = $this->from(route('cart.index'))->patch(route('cart.update', $product), ['quantity' => 5]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHasErrors('quantity');
        $response->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_cart_line_can_be_removed(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->withSession(['cart' => [$product->id => 2]]);

        $response = $this->delete(route('cart.destroy', $product));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('cart', []);
    }

    public function test_cart_total_is_calculated_from_stored_prices(): void
    {
        $first = Product::factory()->create(['stock' => 5, 'price' => 1_000_000]);
        $second = Product::factory()->create(['stock' => 5, 'price' => 250_000]);

        $this->withSession(['cart' => [$first->id => 2, $second->id => 3]]);

        $response = $this->get(route('cart.index'));

        $response->assertOk();
        // 2 x 1.000.000 + 3 x 250.000
        $this->assertSame(2_750_000.0, $response->viewData('subtotal'));
        $response->assertSee('2.750.000');
    }

    public function test_a_product_removed_from_the_catalogue_is_dropped_from_the_cart_view(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->withSession(['cart' => [$product->id => 2]]);

        $product->forceFill(['is_active' => false])->save();

        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Keranjang Anda masih kosong');
        $this->assertSame([], $response->viewData('lines'));
    }
}
