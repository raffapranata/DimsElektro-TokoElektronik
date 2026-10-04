<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Demo order history so the admin dashboard and the customer order pages are
 * meaningful on a fresh development database.
 */
class OrderSeeder extends Seeder
{
    /**
     * Order status paired with the payment status that fits it.
     *
     * @var array<int, array{0: OrderStatus, 1: PaymentStatus}>
     */
    private const SCENARIOS = [
        [OrderStatus::Pending, PaymentStatus::Pending],
        [OrderStatus::Processing, PaymentStatus::Paid],
        [OrderStatus::Shipped, PaymentStatus::Paid],
        [OrderStatus::Completed, PaymentStatus::Paid],
        [OrderStatus::Cancelled, PaymentStatus::Expired],
    ];

    public function run(): void
    {
        $customers = User::query()->where('role', UserRole::Customer->value)->get();
        $products = Product::query()->where('is_active', true)->where('stock', '>', 0)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            foreach (self::SCENARIOS as [$orderStatus, $paymentStatus]) {
                $this->createOrder($customer, $products, $orderStatus, $paymentStatus);
            }
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createOrder(
        User $customer,
        Collection $products,
        OrderStatus $orderStatus,
        PaymentStatus $paymentStatus,
    ): void {
        $lines = [];
        $subtotal = 0.0;

        foreach ($products->random(min(2, $products->count())) as $product) {
            if ($product->stock < 1) {
                continue;
            }

            // One unit per line keeps the demo catalogue from being drained
            // by the seeded order history.
            $quantity = min(1, $product->stock);
            $lineSubtotal = $product->subtotalFor($quantity);

            $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $lineSubtotal];
            $subtotal += $lineSubtotal;
        }

        if ($lines === []) {
            return;
        }

        $order = Order::factory()->withStatus($orderStatus)->create([
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'recipient_phone' => fake()->numerify('08##########'),
            'shipping_address' => fake()->streetAddress().', '.fake()->city().', Indonesia',
            'subtotal' => $subtotal,
            'shipping_cost' => 0,
            'total_amount' => $subtotal,
        ]);

        foreach ($lines as $line) {
            OrderItem::factory()
                ->forProduct($line['product'], $line['quantity'])
                ->create(['order_id' => $order->id]);

            // Keep the in-memory stock in step so later orders cannot oversell.
            $product = $line['product'];
            $product->stock -= $line['quantity'];
            $product->save();
        }

        Payment::factory()->withStatus($paymentStatus)->create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'payment_type' => $paymentStatus === PaymentStatus::Pending ? null : 'bank_transfer',
        ]);
    }
}
