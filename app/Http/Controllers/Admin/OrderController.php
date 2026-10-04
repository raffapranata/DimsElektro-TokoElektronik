<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\InvalidPaymentStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Orders shown per page in the admin list.
     */
    private const int PER_PAGE = 15;

    public function __construct(private readonly OrderService $orders) {}

    public function index(): View
    {
        $orders = Order::query()
            ->with(['user:id,name,email', 'payment'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'payment', 'user']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        try {
            if ($request->orderStatus() !== null) {
                $this->orders->updateStatus($order, $request->orderStatus());
            }

            if ($request->paymentStatus() !== null) {
                $this->orders->updatePaymentStatus($order, $request->paymentStatus());
            }
        } catch (InvalidOrderStatusTransitionException|InvalidPaymentStatusTransitionException $exception) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', $exception->userMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Status pesanan berhasil diperbarui.');
    }
}
