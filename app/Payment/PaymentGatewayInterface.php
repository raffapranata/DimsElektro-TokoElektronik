<?php

namespace App\Payment;

use App\Models\Order;

/**
 * Boundary between checkout and whatever actually collects the money.
 *
 * Checkout depends on this contract rather than on a concrete gateway, so the
 * Midtrans adapter can be swapped in without touching CheckoutService.
 */
interface PaymentGatewayInterface
{
    /**
     * Prepare a payment for the given order.
     *
     * Implementations must not mutate the order. They return a PaymentResult
     * that the caller persists as the order's payment record.
     */
    public function createPayment(Order $order): PaymentResult;

    /**
     * Identifier of the active gateway, stored on the payment for auditing.
     */
    public function name(): string;
}
