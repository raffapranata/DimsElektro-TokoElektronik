<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The gateway adapter that the checkout service resolves. "fake" performs
    | no network calls and needs no credentials, which is why it is the default:
    | the store must keep working with Midtrans unconfigured.
    |
    | Supported: "fake", "midtrans"
    |
    */

    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

    'currency' => env('PAYMENT_CURRENCY', 'IDR'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans
    |--------------------------------------------------------------------------
    |
    | Placeholders only. Selecting the "midtrans" gateway without filling these
    | in raises PaymentGatewayNotConfiguredException instead of failing at boot.
    |
    */

    'midtrans' => [
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),
    ],

];
