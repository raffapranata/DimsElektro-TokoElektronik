<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\InvalidPaymentStatusTransitionException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Every order status change goes through here, so the lifecycle rules and the
 * stock side effects live in exactly one place.
 */
class OrderService
{
    /**
     * Move an order to a new status, restoring stock when it is cancelled.
     *
     * @throws InvalidOrderStatusTransitionException
     */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        return DB::transaction(function () use ($order, $status): Order {
            // Re-read the order under a lock: two admins acting at the same time
            // must not both pass the transition check.
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status === $status) {
                return $locked;
            }

            if (! $locked->canTransitionTo($status)) {
                throw new InvalidOrderStatusTransitionException($locked->status, $status);
            }

            if ($status === OrderStatus::Cancelled) {
                $this->restoreStock($locked);
                $this->cancelPendingPayment($locked);
            }

            $locked->status = $status;
            $locked->save();

            return $locked;
        });
    }

    /**
     * Update the payment status of an order (manual verification by an admin).
     *
     * @throws InvalidPaymentStatusTransitionException
     */
    public function updatePaymentStatus(Order $order, PaymentStatus $status): Order
    {
        return DB::transaction(function () use ($order, $status): Order {
            $locked = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $payment = $locked->payment()->lockForUpdate()->first();

            if ($payment === null) {
                throw InvalidPaymentStatusTransitionException::missingPayment();
            }

            if ($payment->status === $status) {
                return $locked->load('items', 'payment');
            }

            if (! $payment->status->canTransitionTo($status)) {
                throw InvalidPaymentStatusTransitionException::between($payment->status, $status);
            }

            $payment->status = $status;
            $payment->paid_at = $status === PaymentStatus::Paid ? now() : null;
            $payment->save();

            return $locked->load('items', 'payment');
        });
    }

    /**
     * Return reserved stock to the catalogue. Only called for orders that were
     * still open, so the same stock is never restored twice.
     */
    private function restoreStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

            if ($product === null) {
                continue;
            }

            $product->increment('stock', $item->quantity);
        }
    }

    /**
     * A payment that was never settled cannot outlive a cancelled order.
     * A payment that was already made is left untouched on purpose: refunding
     * money is a deliberate admin action, not a side effect of cancelling.
     */
    private function cancelPendingPayment(Order $order): void
    {
        $payment = $order->payment()->lockForUpdate()->first();

        if ($payment === null || ! $payment->isPending()) {
            return;
        }

        if ($payment->status->canTransitionTo(PaymentStatus::Cancelled)) {
            $payment->status = PaymentStatus::Cancelled;
            $payment->save();
        }
    }
}
