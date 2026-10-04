<?php

namespace App\Exceptions;

use App\Enums\PaymentStatus;
use RuntimeException;

/**
 * Raised when a payment is asked to move to a status the lifecycle forbids, or
 * when an order has no payment record to update at all.
 */
class InvalidPaymentStatusTransitionException extends RuntimeException
{
    private function __construct(
        string $message,
        public readonly ?PaymentStatus $from = null,
        public readonly ?PaymentStatus $to = null,
    ) {
        parent::__construct($message);
    }

    public static function between(PaymentStatus $from, PaymentStatus $to): self
    {
        return new self(
            sprintf('Status pembayaran tidak dapat diubah dari "%s" ke "%s".', $from->value, $to->value),
            $from,
            $to,
        );
    }

    public static function missingPayment(): self
    {
        return new self('Pesanan ini belum memiliki data pembayaran.');
    }

    public function userMessage(): string
    {
        if ($this->from === null || $this->to === null) {
            return $this->getMessage();
        }

        return sprintf(
            'Status pembayaran "%s" tidak dapat diubah ke "%s".',
            $this->from->label(),
            $this->to->label(),
        );
    }
}
