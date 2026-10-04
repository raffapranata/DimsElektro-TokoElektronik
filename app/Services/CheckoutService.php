<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Payment\PaymentGatewayInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns the session cart into an order.
 *
 * Everything that must succeed together happens inside one transaction: the
 * order, its items, the stock decrement and the pending payment. A failure at
 * any step rolls the whole thing back, so a customer can never end up with an
 * order without items, or stock reduced without an order.
 */
class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentGatewayInterface $paymentGateway,
        private readonly OrderNumberGenerator $orderNumbers,
    ) {}

    /**
     * @param  array{recipient_name: string, recipient_phone: string, shipping_address: string, notes?: string|null}  $shipping
     *
     * @throws EmptyCartException
     * @throws ProductUnavailableException
     * @throws InsufficientStockException
     */
    public function placeOrder(User $user, array $shipping): Order
    {
        $cart = $this->cart->raw();

        if ($cart === []) {
            throw EmptyCartException::make();
        }

        $order = DB::transaction(function () use ($user, $shipping, $cart): Order {
            // Lock the product rows for the duration of the transaction. A
            // concurrent checkout of the same product blocks here, which is what
            // makes the stock check below trustworthy.
            /** @var Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0.0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);

                if ($product === null) {
                    throw ProductUnavailableException::make();
                }

                if (! $product->is_active) {
                    throw ProductUnavailableException::make($product->name);
                }

                if ($product->stock < $quantity) {
                    throw new InsufficientStockException($product, $quantity);
                }

                // Prices come from the database only, never from the request.
                $lineSubtotal = $product->subtotalFor($quantity);

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $lineSubtotal,
                ];

                $subtotal += $lineSubtotal;
            }

            $subtotal = round($subtotal, 2);
            $shippingCost = round((float) config('store.shipping_cost'), 2);
            $totalAmount = round($subtotal + $shippingCost, 2);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->orderNumbers->generate(),
                'status' => OrderStatus::Pending,
                'recipient_name' => $shipping['recipient_name'],
                'recipient_phone' => $shipping['recipient_phone'],
                'shipping_address' => $shipping['shipping_address'],
                'notes' => $shipping['notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    // Snapshot: a later price change must not rewrite history.
                    'price' => $line['product']->price,
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            // The default gateway is local (no network I/O), so it is safe to
            // call while the lock is held. A gateway that performs slow outbound
            // requests should instead be called after commit and then update this
            // payment record, to keep the lock window short.
            $result = $this->paymentGateway->createPayment($order);

            $paymentAttributes = $result->toPaymentAttributes();
            // Record which gateway produced the record, for auditing.
            $paymentAttributes['raw_response'] = array_merge(
                ['gateway' => $this->paymentGateway->name()],
                $result->rawResponse,
            );

            $order->payment()->create([
                'amount' => $totalAmount,
                ...$paymentAttributes,
            ]);

            return $order->load('items', 'payment');
        });

        // Only clear the cart once the transaction has actually committed.
        $this->cart->clear();

        return $order;
    }
}
