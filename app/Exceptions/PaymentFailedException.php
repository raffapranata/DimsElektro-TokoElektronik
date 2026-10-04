<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised by a gateway adapter when it cannot create a payment.
 *
 * Checkout catches this, rolls the whole transaction back and lets the customer
 * retry, so a transient provider problem never leaves a half-built order behind.
 */
class PaymentFailedException extends RuntimeException
{
    public static function make(string $reason): self
    {
        return new self('Pembayaran gagal diproses: '.$reason);
    }

    public function userMessage(): string
    {
        return 'Pembayaran Anda gagal diproses. Silakan coba beberapa saat lagi.';
    }
}
