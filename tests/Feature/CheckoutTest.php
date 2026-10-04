<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentFailedException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Payment\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validShipping(): array
    {
        return [
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 10, Bandung, Jawa Barat',
        ];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), $this->validShipping())->assertRedirect(route('login'));
    }

    public function test_checkout_page_redirects_to_the_cart_when_empty(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get(route('checkout.create'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error', 'Keranjang Anda masih kosong.');
    }

    public function test_submitting_an_empty_cart_creates_nothing(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validShipping());

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_placing_an_order_creates_order_items_payment_and_reduces_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $this->validShipping());

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionMissing('cart');

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('2000000.00', $order->subtotal);
        $this->assertSame('2000000.00', $order->total_amount);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => '1000000.00',
            'subtotal' => '2000000.00',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => PaymentStatus::Pending->value,
            'amount' => '2000000.00',
        ]);

        $this->assertSame(8, $product->refresh()->stock);
    }

    public function test_order_items_snapshot_the_price_paid(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->validShipping());

        // The catalogue price changes after the order was placed.
        $product->update(['price' => 1_500_000]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'price' => '1000000.00',
        ]);

        $this->assertSame('1000000.00', Order::query()->firstOrFail()->total_amount);
    }

    public function test_shipping_cost_is_added_to_the_order_total(): void
    {
        config()->set('store.shipping_cost', 25_000);

        $product = Product::factory()->create(['stock' => 10, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->validShipping());

        $order = Order::query()->firstOrFail();

        $this->assertSame('1000000.00', $order->subtotal);
        $this->assertSame('25000.00', $order->shipping_cost);
        $this->assertSame('1025000.00', $order->total_amount);
    }

    public function test_multiple_products_are_summed_into_one_order(): void
    {
        $keyboard = Product::factory()->create(['stock' => 5, 'price' => 1_500_000]);
        $mouse = Product::factory()->create(['stock' => 5, 'price' => 300_000]);
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$keyboard->id => 1, $mouse->id => 2]])
            ->post(route('checkout.store'), $this->validShipping());

        $order = Order::query()->firstOrFail();

        $this->assertSame(2, $order->items()->count());
        $this->assertSame('2100000.00', $order->subtotal);
        $this->assertSame(4, $keyboard->refresh()->stock);
        $this->assertSame(3, $mouse->refresh()->stock);
    }

    public function test_insufficient_stock_rolls_back_the_entire_checkout(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        // The session claims 5 units even though only 2 exist.
        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 5]])
            ->post(route('checkout.store'), $this->validShipping());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(2, $product->refresh()->stock);
    }

    public function test_a_deactivated_product_blocks_checkout_without_side_effects(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $session = ['cart' => [$product->id => 1]];

        $product->update(['is_active' => false]);

        $response = $this->actingAs($user)
            ->withSession($session)
            ->post(route('checkout.store'), $this->validShipping());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->refresh()->stock);
    }

    public function test_a_payment_failure_rolls_back_the_whole_checkout(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $this->mock(PaymentGatewayInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('name')->andReturn('failing');
            $mock->shouldReceive('createPayment')
                ->once()
                ->andThrow(PaymentFailedException::make('gateway tidak tersedia'));
        });

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $this->validShipping());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // No order may survive without its payment record.
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(10, $product->refresh()->stock);
    }

    public function test_checkout_locks_the_product_rows_before_adjusting_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 1_000_000]);
        $user = User::factory()->customer()->create();

        $statements = [];

        DB::listen(function ($query) use (&$statements): void {
            $statements[] = $query->sql;
        });

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->validShipping());

        $lockedProductRead = collect($statements)->contains(
            fn (string $sql): bool => str_contains($sql, 'from `products`')
                && str_contains(strtolower($sql), 'for update'),
        );

        $this->assertTrue($lockedProductRead, 'Checkout must lock the product rows with FOR UPDATE.');
    }

    public function test_shipping_details_are_required(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), []);

        $response->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'shipping_address']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_an_invalid_phone_number_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                ...$this->validShipping(),
                'recipient_phone' => 'abcdefghij',
            ]);

        $response->assertSessionHasErrors('recipient_phone');
        $this->assertDatabaseCount('orders', 0);
    }
}
