<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Orders shown per page in the customer history.
     */
    private const int PER_PAGE = 10;

    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('payment')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'payment']);

        return view('orders.show', ['order' => $order]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->orders->updateStatus($order, OrderStatus::Cancelled);
        } catch (InvalidOrderStatusTransitionException $exception) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', $exception->userMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Pesanan berhasil dibatalkan dan stok dikembalikan.');
    }
}
