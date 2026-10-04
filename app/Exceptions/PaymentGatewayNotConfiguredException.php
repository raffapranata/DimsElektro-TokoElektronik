<?php

namespace App\Exceptions;

use LogicException;

/**
 * Raised when a payment gateway is selected but cannot be used yet.
 *
 * This is a configuration problem, not a user error, and it must never stop the
 * application from booting with the default "fake" gateway.
 */
class PaymentGatewayNotConfiguredException extends LogicException
{
    //
}
