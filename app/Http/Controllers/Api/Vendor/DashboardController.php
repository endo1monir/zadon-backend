<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Resources\OrderResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\VendorProductResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseController
{
    public function overview(): JsonResponse
    {
        $store = $this->managedStore()->load('category');

        $todayRevenue = $store->orders()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $lowStock = $store->products()
            ->with('category')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock_alert')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return $this->successReturn([
            'store' => new StoreResource($store),
            'kpi' => [
                'today_revenue' => (float) round($todayRevenue, 2),
                'today_orders_count' => $store->orders()->whereDate('created_at', today())->count(),
                'active_orders_count' => $store->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
                'low_stock_count' => $store->products()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock_alert')->count(),
                'out_of_stock_count' => $store->products()->where('stock', 0)->count(),
                'total_products' => $store->products()->count(),
                'total_units' => (int) $store->products()->sum('stock'),
            ],
            'recent_orders' => OrderResource::collection(
                $store->orders()->with('items', 'paymentMethod', 'review')->latest()->limit(5)->get()
            ),
            'low_stock_products' => VendorProductResource::collection($lowStock),
        ]);
    }
}
