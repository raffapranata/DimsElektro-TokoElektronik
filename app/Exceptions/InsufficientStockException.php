<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

/**
 * Thrown inside the checkout transaction when the row lock reveals that the
 * requested quantity is no longer available.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requestedQuantity,
    ) {
        parent::__construct(sprintf(
            'Stok produk "%s" tidak mencukupi. Tersedia %d, diminta %d.',
            $product->name,
            $product->stock,
            $requestedQuantity,
        ));
    }

    public function userMessage(): string
    {
        return sprintf(
            'Stok produk "%s" tidak mencukupi. Tersedia %d unit, Anda meminta %d unit.',
            $this->product->name,
            $this->product->stock,
            $this->requestedQuantity,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'product_id' => $this->product->id,
            'available_stock' => $this->product->stock,
            'requested_quantity' => $this->requestedQuantity,
        ];
    }
}
