<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetOverviewTool extends AdminTool
{
    protected function key(): string
    {
        return 'get_overview';
    }

    public function handle(Request $request): Response
    {
        $now = Carbon::now();

        return $this->json([
            'totals' => [
                'customers' => User::customer()->count(),
                'vendors' => User::vendor()->count(),
                'stores' => Store::count(),
                'active_stores' => Store::active()->count(),
                'products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'out_of_stock_products' => Product::where('stock', '<=', 0)->count(),
                'orders' => Order::count(),
                'revenue_sar' => $this->revenue(Order::query()),
            ],
            'new_orders_waiting' => Order::where('status', 'new')->count(),
            'orders_by_status' => Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'today' => $this->summary(Order::query()->whereBetween('created_at', [$now->copy()->startOfDay(), $now])),
            'this_month' => $this->summary(Order::query()->whereBetween('created_at', [$now->copy()->startOfMonth(), $now])),
            'growth_last_30_days_percent' => [
                'customers' => $this->growth(User::customer(), $now),
                'orders' => $this->growth(Order::query(), $now),
                'revenue' => $this->growth(Order::query()->where('status', '!=', 'cancelled'), $now, 'total'),
            ],
        ]);
    }

    /**
     * @param  Builder<Order>  $orders
     * @return array{orders: int, revenue_sar: float}
     */
    private function summary(Builder $orders): array
    {
        return ['orders' => (clone $orders)->count(), 'revenue_sar' => $this->revenue($orders)];
    }

    /**
     * @param  Builder<Order>  $orders
     */
    private function revenue(Builder $orders): float
    {
        return $this->money((clone $orders)->where('status', '!=', 'cancelled')->sum('total'));
    }

    /**
     * Last 30 days against the 30 days before, as a percentage.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function growth(Builder $query, Carbon $now, ?string $sumColumn = null): float
    {
        $measure = fn (Carbon $from, Carbon $to): float => $sumColumn
            ? (float) (clone $query)->whereBetween('created_at', [$from, $to])->sum($sumColumn)
            : (float) (clone $query)->whereBetween('created_at', [$from, $to])->count();

        $current = $measure($now->copy()->subDays(29)->startOfDay(), $now);
        $previous = $measure($now->copy()->subDays(59)->startOfDay(), $now->copy()->subDays(30)->endOfDay());

        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
