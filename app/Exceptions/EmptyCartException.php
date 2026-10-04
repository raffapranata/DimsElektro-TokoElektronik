<?php

namespace App\Exceptions;

use RuntimeException;

class EmptyCartException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Keranjang Anda masih kosong.');
    }
}
