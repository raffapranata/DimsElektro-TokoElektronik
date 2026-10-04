<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'shippingCost' => (float) config('store.shipping_cost'),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        $quantity = $this->cart->add($product, $request->integer('quantity'));

        return redirect()
            ->route('cart.index')
            ->with('status', sprintf('%s (%d unit) ditambahkan ke keranjang.', $product->name, $quantity));
    }

    public function update(UpdateCartItemRequest $request, Product $product): RedirectResponse
    {
        $quantity = $this->cart->setQuantity($product, $request->integer('quantity'));

        return redirect()
            ->route('cart.index')
            ->with('status', sprintf('Jumlah %s diperbarui menjadi %d unit.', $product->name, $quantity));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()
            ->route('cart.index')
            ->with('status', sprintf('%s dihapus dari keranjang.', $product->name));
    }
}
