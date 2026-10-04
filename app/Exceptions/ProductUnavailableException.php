<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a cart line points at a product that was deactivated or removed
 * between the moment it was added and checkout.
 */
class ProductUnavailableException extends RuntimeException
{
    public static function make(?string $productName = null): self
    {
        if ($productName === null) {
            return new self('Salah satu produk di keranjang Anda sudah tidak tersedia dan dihapus.');
        }

        return new self(sprintf(
            'Produk "%s" sudah tidak dijual lagi dan dihapus dari keranjang Anda.',
            $productName,
        ));
    }
}
