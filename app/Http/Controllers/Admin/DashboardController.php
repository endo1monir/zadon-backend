<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        return view('admin.dashboard.index', [
            'stats' => [
                'users' => User::count(),
                'stores' => Store::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'revenue' => (float) Order::whereNotIn('status', ['cancelled'])->sum('total'),
                'pending_orders' => Order::where('status', 'new')->count(),
            ],
            'growth' => [
                'users' => $this->countGrowth(User::query(), $now),
                'stores' => $this->countGrowth(Store::query(), $now),
                'products' => $this->countGrowth(Product::query(), $now),
                'orders' => $this->countGrowth(Order::query(), $now),
            ],
            'recentOrders' => Order::query()
                ->with(['user:id,name', 'store:id,name_ar,name_en'])
                ->latest()
                ->limit(5)
                ->get(),
            'topStores' => Store::query()
                ->withCount('products')
                ->orderByDesc('products_count')
                ->orderByDesc('rating')
                ->limit(5)
                ->get(['id', 'name_ar', 'name_en', 'logo', 'rating', 'is_active']),
            'lowStockProducts' => Product::query()
                ->orderBy('stock')
                ->limit(5)
                ->get(['id', 'name_ar', 'name_en', 'stock', 'price']),
            'ordersChart' => $this->ordersChart($now),
        ]);
    }

    private function countGrowth(Builder $query, Carbon $now): float
    {
        $current = (clone $query)->whereBetween('created_at', [$now->copy()->subDays(29), $now])->count();
        $previous = (clone $query)->whereBetween('created_at', [$now->copy()->subDays(59), $now->copy()->subDays(30)])->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function ordersChart(Carbon $now): array
    {
        $rows = Order::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
            ->whereBetween('created_at', [$now->copy()->subDays(29), $now])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('aggregate', 'day');

        $labels = [];
        $series = [];

        for ($offset = 29; $offset >= 0; $offset--) {
            $date = $now->copy()->subDays($offset);
            $labels[] = $date->format('M j');
            $series[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return compact('labels', 'series');
    }
}
