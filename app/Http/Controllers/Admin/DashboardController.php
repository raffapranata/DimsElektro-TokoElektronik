<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Recent orders shown on the dashboard.
     */
    private const int RECENT_ORDERS = 8;

    /**
     * Low stock products shown on the dashboard.
     */
    private const int LOW_STOCK_LIMIT = 8;

    public function index(): View
    {
        $lowStockThreshold = (int) config('store.low_stock_threshold');

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'activeProducts' => Product::query()->where('is_active', true)->count(),
                'orders' => Order::query()->count(),
                'pendingOrders' => Order::query()->where('status', OrderStatus::Pending)->count(),
                'completedOrders' => Order::query()->where('status', OrderStatus::Completed)->count(),
                'completedRevenue' => (float) Order::query()
                    ->where('status', OrderStatus::Completed)
                    ->sum('total_amount'),
            ],
            'lowStockThreshold' => $lowStockThreshold,
            'lowStockProducts' => Product::query()
                ->where('is_active', true)
                ->lowStock($lowStockThreshold)
                ->with('category:id,name,slug')
                ->orderBy('stock')
                ->orderBy('name')
                ->limit(self::LOW_STOCK_LIMIT)
                ->get(),
            'recentOrders' => Order::query()
                ->with(['user:id,name', 'payment'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(self::RECENT_ORDERS)
                ->get(),
        ]);
    }
}
