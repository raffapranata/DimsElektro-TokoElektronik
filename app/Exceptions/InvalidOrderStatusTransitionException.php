<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;

/**
 * Raised when an order is asked to move to a status that the lifecycle does not
 * allow (for example shipping a cancelled order, or cancelling a shipped one).
 */
class InvalidOrderStatusTransitionException extends RuntimeException
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        parent::__construct(sprintf(
            'Status pesanan tidak dapat diubah dari "%s" ke "%s".',
            $from->value,
            $to->value,
        ));
    }

    public function userMessage(): string
    {
        return sprintf(
            'Pesanan berstatus "%s" tidak dapat diubah ke "%s".',
            $this->from->label(),
            $this->to->label(),
        );
    }
}
