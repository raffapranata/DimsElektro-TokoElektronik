<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\ProductUnavailableException;
use App\Http\Requests\CheckoutRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        // Drop lines that stopped being purchasable so the summary the customer
        // confirms matches what checkout will actually charge.
        $this->cart->prune();

        $lines = $this->cart->lines();

        if ($lines === []) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang Anda masih kosong.');
        }

        return view('checkout.create', [
            'lines' => $lines,
            'subtotal' => $this->cart->subtotal(),
            'shippingCost' => (float) config('store.shipping_cost'),
            'user' => $request->user(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $order = $this->checkout->placeOrder($user, $request->validated());
        } catch (EmptyCartException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        } catch (ProductUnavailableException $exception) {
            $this->cart->prune();

            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        } catch (InsufficientStockException $exception) {
            $this->cart->prune();

            return redirect()->route('cart.index')->with('error', $exception->userMessage());
        } catch (PaymentFailedException $exception) {
            // The transaction is already rolled back, so nothing was charged and
            // no order exists; the customer can simply try again.
            return redirect()->route('cart.index')->with('error', $exception->userMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', sprintf(
                'Pesanan %s berhasil dibuat. Selesaikan pembayaran Anda.',
                $order->order_number,
            ));
    }
}
