<?php

namespace App\Payment;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Local stand-in for a real payment provider.
 *
 * It performs no network calls and needs no credentials, so the whole checkout
 * flow stays testable and the store runs with Midtrans unconfigured. A payment
 * is created in the pending state and settled later (by an admin, a webhook, or
 * a manual transfer confirmation).
 */
final class FakePaymentGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'fake';
    }

    public function createPayment(Order $order): PaymentResult
    {
        return PaymentResult::pending(
            transactionId: 'FAKE-'.Str::upper(Str::random(12)),
            paymentType: 'manual_transfer',
            rawResponse: [
                'order_number' => $order->order_number,
                'amount' => (float) $order->total_amount,
                'currency' => config('payment.currency'),
                'instructions' => 'Selesaikan pembayaran melalui transfer bank, lalu tunggu verifikasi.',
            ],
        );
    }
}
