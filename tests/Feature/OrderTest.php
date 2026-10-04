<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customers_can_view_their_order_history(): void
    {
        $customer = User::factory()->customer()->create();
        Order::factory()->count(2)->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->get(route('orders.index'));

        $response->assertOk();
        $response->assertSee($customer->orders()->first()->order_number);
    }

    public function test_the_order_history_excludes_other_customers_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $foreignOrder = Order::factory()->create(['user_id' => $stranger->id]);

        $response = $this->actingAs($customer)->get(route('orders.index'));

        $response->assertOk();
        $response->assertDontSee($foreignOrder->order_number);
    }

    public function test_a_customer_cannot_view_another_customers_order_detail(): void
    {
        $customer = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $foreignOrder = Order::factory()->create(['user_id' => $stranger->id]);

        $this->actingAs($customer)
            ->get(route('orders.show', $foreignOrder))
            ->assertForbidden();
    }

    public function test_a_customer_can_view_their_own_order_detail(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderItem::factory()->forProduct(Product::factory()->create())->create(['order_id' => $order->id]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_guests_are_redirected_to_login_when_viewing_orders(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_cancelling_a_pending_order_restores_the_stock(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $order = Order::factory()->pending()->create(['user_id' => $customer->id]);
        OrderItem::factory()->forProduct($product, 2)->create(['order_id' => $order->id]);
        Payment::factory()->for($order)->create(['status' => PaymentStatus::Pending]);

        $product->decrement('stock', 2);
        $this->assertSame(1, $product->refresh()->stock);

        $response = $this->actingAs($customer)->post(route('orders.cancel', $order));

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
        $this->assertSame(3, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Cancelled, $order->payment->status);
    }

    public function test_cancelling_a_processing_order_is_allowed(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->processing()->create(['user_id' => $customer->id]);
        Product::factory()->create();

        OrderItem::factory()->forProduct(Product::query()->first())->create(['order_id' => $order->id]);

        $response = $this->actingAs($customer)->post(route('orders.cancel', $order));

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
    }

    public function test_a_shipped_order_cannot_be_cancelled(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->shipped()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->post(route('orders.cancel', $order));

        // The policy refuses the action outright: a shipped order is on its way.
        $response->assertForbidden();
        $this->assertSame(OrderStatus::Shipped, $order->refresh()->status);
    }

    public function test_a_final_order_cannot_be_cancelled(): void
    {
        $customer = User::factory()->customer()->create();

        foreach ([OrderStatus::Completed, OrderStatus::Cancelled] as $status) {
            $order = Order::factory()->withStatus($status)->create(['user_id' => $customer->id]);

            $this->actingAs($customer)
                ->post(route('orders.cancel', $order))
                ->assertForbidden();

            $this->assertSame($status, $order->refresh()->status);
        }
    }

    public function test_a_customer_cannot_cancel_another_customers_order(): void
    {
        $customer = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $foreignOrder = Order::factory()->pending()->create(['user_id' => $stranger->id]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $foreignOrder))
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $foreignOrder->refresh()->status);
    }

    public function test_the_cancel_button_only_appears_on_cancellable_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $pending = Order::factory()->pending()->create(['user_id' => $customer->id]);
        $shipped = Order::factory()->shipped()->create(['user_id' => $customer->id]);

        $this->actingAs($customer)->get(route('orders.show', $pending))
            ->assertOk()
            ->assertSee('Batalkan pesanan');

        $this->actingAs($customer)->get(route('orders.show', $shipped))
            ->assertOk()
            ->assertDontSee('Batalkan pesanan');
    }
}
