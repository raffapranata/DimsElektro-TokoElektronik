<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;

/**
 * Session backed shopping cart.
 *
 * Only product ids and quantities are stored. Prices, line subtotals and the
 * cart total are always resolved from the database, so a tampered session can
 * never influence what the customer is charged.
 */
class CartService
{
    private const string SESSION_KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /**
     * Raw cart contents.
     *
     * @return array<int, int> product id => quantity
     */
    public function raw(): array
    {
        $cart = $this->session->get(self::SESSION_KEY, []);

        if (! is_array($cart)) {
            return [];
        }

        $normalised = [];

        foreach ($cart as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($productId > 0 && $quantity > 0) {
                $normalised[$productId] = $quantity;
            }
        }

        return $normalised;
    }

    /**
     * Add to an existing line and return the resulting quantity.
     */
    public function add(Product $product, int $quantity): int
    {
        $cart = $this->raw();

        $cart[$product->id] = $this->capToStock(
            ($cart[$product->id] ?? 0) + max(1, $quantity),
            $product,
        );

        $this->persist($cart);

        return $cart[$product->id];
    }

    /**
     * Replace the quantity of a single line.
     */
    public function setQuantity(Product $product, int $quantity): int
    {
        $cart = $this->raw();

        $cart[$product->id] = $this->capToStock(max(1, $quantity), $product);

        $this->persist($cart);

        return $cart[$product->id];
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();

        unset($cart[$product->id]);

        $this->persist($cart);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * Drop lines whose product is gone, deactivated, or fully out of stock.
     */
    public function prune(): void
    {
        $cart = $this->raw();

        if ($cart === []) {
            return;
        }

        $purchasableIds = Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->pluck('id')
            ->all();

        $this->persist(array_intersect_key($cart, array_flip($purchasableIds)));
    }

    public function totalQuantity(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->totalQuantity() === 0;
    }

    /**
     * Hydrated cart contents, ready to render.
     *
     * Products that were deactivated or deleted after being added are silently
     * dropped; they are re-validated again at checkout.
     *
     * @return array<int, array{product: Product, quantity: int, subtotal: float}>
     */
    public function lines(): array
    {
        $cart = $this->raw();

        if ($cart === []) {
            return [];
        }

        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->with('category:id,name,slug')
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);

            if ($product === null) {
                continue;
            }

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $product->subtotalFor($quantity),
            ];
        }

        return $lines;
    }

    /**
     * Cart total, always derived from stored prices.
     */
    public function subtotal(): float
    {
        return round(array_sum(array_column($this->lines(), 'subtotal')), 2);
    }

    /**
     * Keep the session consistent with the stock known at this moment. This is
     * a usability guard only; checkout re-checks stock under a row lock.
     */
    private function capToStock(int $quantity, Product $product): int
    {
        return max(0, min($quantity, $product->stock));
    }

    /**
     * @param  array<int, int>  $cart
     */
    private function persist(array $cart): void
    {
        $cart = array_filter($cart, fn (int $quantity): bool => $quantity > 0);

        $this->session->put(self::SESSION_KEY, $cart);
    }
}
