<?php

namespace App\Payment;

use App\Exceptions\PaymentGatewayNotConfiguredException;
use App\Models\Order;

/**
 * Midtrans adapter.
 *
 * Midtrans is intentionally not wired up in this project: there are no
 * credentials and no outbound calls. The class exists so the architecture is
 * ready, and it fails loudly and clearly if it is selected without the
 * credentials it needs, rather than breaking at application boot.
 *
 * To enable it later:
 *   1. composer require midtrans/midtrans-php
 *   2. fill MIDTRANS_SERVER_KEY / MIDTRANS_CLIENT_KEY / MIDTRANS_MERCHANT_ID
 *   3. set PAYMENT_GATEWAY=midtrans
 *   4. implement createPayment() below using Snap::createTransaction()
 */
final class MidtransPaymentGateway implements PaymentGatewayInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config) {}

    public function name(): string
    {
        return 'midtrans';
    }

    public function createPayment(Order $order): PaymentResult
    {
        $this->ensureIsConfigured();

        // Deliberately unreachable until Snap is implemented: reaching this line
        // means credentials were supplied but no implementation exists yet.
        throw new PaymentGatewayNotConfiguredException(
            'Gateway Midtrans belum diimplementasikan. Gunakan PAYMENT_GATEWAY=fake untuk saat ini.'
        );
    }

    /**
     * @throws PaymentGatewayNotConfiguredException
     */
    private function ensureIsConfigured(): void
    {
        foreach (['server_key', 'client_key'] as $key) {
            if (blank($this->config[$key] ?? null)) {
                throw new PaymentGatewayNotConfiguredException(
                    'Gateway Midtrans dipilih tetapi MIDTRANS_'.strtoupper($key).' belum diisi.'
                );
            }
        }
    }
}
