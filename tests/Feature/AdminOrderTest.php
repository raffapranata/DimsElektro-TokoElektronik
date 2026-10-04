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

class AdminOrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_the_order_list(): void
    {
        $admin = User::factory()->admin()->create();
        $orders = Order::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk();
        foreach ($orders as $order) {
            $response->assertSee($order->order_number);
        }
    }

    public function test_a_customer_cannot_open_the_admin_order_list(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_when_opening_the_admin_order_list(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_an_order_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create(['name' => 'Siti Rahayu']);
        $product = Product::factory()->create(['name' => 'Laptop Ultra 14']);
        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderItem::factory()->forProduct($product, 2)->create(['order_id' => $order->id]);
        Payment::factory()->for($order)->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Siti Rahayu');
        $response->assertSee('Laptop Ultra 14');
        $response->assertSee('Perbarui status');
    }

    public function test_admin_can_move_a_pending_order_to_processing(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => OrderStatus::Processing->value,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(OrderStatus::Processing, $order->refresh()->status);
    }

    public function test_an_illegal_transition_is_rejected_with_a_friendly_message(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->shipped()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), [
                'status' => OrderStatus::Cancelled->value,
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(OrderStatus::Shipped, $order->refresh()->status);
    }

    public function test_cancelling_an_order_from_the_admin_panel_restores_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $order = Order::factory()->pending()->create();
        OrderItem::factory()->forProduct($product, 4)->create(['order_id' => $order->id]);
        Payment::factory()->for($order)->create(['status' => PaymentStatus::Pending]);

        // Mirror the stock decrement that checkout performed (5 - 4 = 1 left).
        $product->decrement('stock', 4);
        $this->assertSame(1, $product->refresh()->stock);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'status' => OrderStatus::Cancelled->value,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
        $this->assertSame(5, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Cancelled, $order->payment->status);
    }

    public function test_admin_can_mark_a_pending_payment_as_paid(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();
        $payment = Payment::factory()->for($order)->create(['status' => PaymentStatus::Pending]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
            'payment_status' => PaymentStatus::Paid->value,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(PaymentStatus::Paid, $payment->refresh()->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_an_invalid_payment_transition_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();
        $payment = Payment::factory()->for($order)->refunded()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), [
                'payment_status' => PaymentStatus::Paid->value,
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(PaymentStatus::Refunded, $payment->refresh()->status);
    }

    public function test_the_update_requires_at_least_one_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), []);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_an_unknown_status_value_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), [
                'status' => 'diantar-rocket',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_a_customer_cannot_update_order_statuses(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->pending()->create(['user_id' => $customer->id]);

        $this->actingAs($customer)
            ->patch(route('admin.orders.update', $order), [
                'status' => OrderStatus::Completed->value,
            ])
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }
}
