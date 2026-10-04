<?php

namespace App\Providers;

use App\Payment\FakePaymentGateway;
use App\Payment\MidtransPaymentGateway;
use App\Payment\PaymentGatewayInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Binds the payment boundary. Nothing outside this provider needs to know which
 * concrete gateway is active.
 */
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function (Application $app): PaymentGatewayInterface {
            $driver = (string) config('payment.gateway');

            return match ($driver) {
                'midtrans' => new MidtransPaymentGateway((array) config('payment.midtrans')),
                // Unknown drivers fall back to the local gateway so a typo in
                // .env cannot take the store offline.
                default => new FakePaymentGateway,
            };
        });
    }
}
