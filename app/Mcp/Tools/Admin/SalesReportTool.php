<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SalesReportTool extends AdminTool
{
    private const GROUPS = ['day', 'store', 'payment_method', 'status'];

    protected function key(): string
    {
        return 'sales_report';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description($this->parameter('from')),
            'to' => $schema->string()->description($this->parameter('to')),
            'group_by' => $schema->string()->enum(self::GROUPS)->description($this->parameter('group_by')),
        ];
    }

    public function handle(Request $request): Response
    {
        [$from, $to] = $this->period($request);
        $groupBy = in_array($request->get('group_by'), self::GROUPS, true) ? $request->get('group_by') : 'day';

        $orders = Order::query()->whereBetween('created_at', [$from, $to]);
        $sold = (clone $orders)->where('status', '!=', 'cancelled');

        $count = (clone $sold)->count();
        $revenue = (float) (clone $sold)->sum('total');

        return $this->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'orders' => $count,
            'revenue_sar' => $this->money($revenue),
            'average_order_sar' => $count > 0 ? $this->money($revenue / $count) : 0.0,
            'vat_collected_sar' => $this->money((clone $sold)->sum('vat_amount')),
            'delivery_fees_sar' => $this->money((clone $sold)->sum('delivery_fee')),
            'cancelled_orders' => (clone $orders)->where('status', 'cancelled')->count(),
            'breakdown_by' => $groupBy,
            'breakdown' => $this->breakdown($groupBy, $orders, $sold),
            'top_products' => $this->topProducts($from, $to),
        ]);
    }

    /**
     * @param  Builder<Order>  $orders
     * @param  Builder<Order>  $sold
     * @return list<array<string, mixed>>
     */
    private function breakdown(string $groupBy, $orders, $sold): array
    {
        if ($groupBy === 'status') {
            return (clone $orders)->selectRaw('status, count(*) as orders, sum(total) as total')
                ->groupBy('status')->orderByDesc('orders')->get()
                ->map(fn ($row): array => ['status' => $row->status, 'orders' => (int) $row->orders, 'total_sar' => $this->money($row->total)])
                ->all();
        }

        $column = match ($groupBy) {
            'store' => 'store_id',
            'payment_method' => 'payment_method',
            default => 'DATE(created_at)',
        };

        $rows = (clone $sold)->selectRaw("{$column} as grp, count(*) as orders, sum(total) as revenue")
            ->groupByRaw($column)
            ->when($groupBy === 'day', fn ($query) => $query->orderBy('grp'), fn ($query) => $query->orderByDesc('revenue'))
            ->get();

        $stores = $groupBy === 'store'
            ? Store::query()->whereKey($rows->pluck('grp')->all())->get(['id', 'name_ar', 'name_en'])->keyBy('id')
            : collect();

        return $rows->map(fn ($row): array => [
            $groupBy => $groupBy === 'store' ? $this->nameOf($stores->get($row->grp)) ?? "#{$row->grp}" : $row->grp,
            ...($groupBy === 'store' ? ['store_id' => (int) $row->grp] : []),
            'orders' => (int) $row->orders,
            'revenue_sar' => $this->money($row->revenue),
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topProducts($from, $to): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_id, max(order_items.product_name_ar) as name_ar, max(order_items.product_name_en) as name_en, sum(order_items.quantity) as quantity, sum(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'product_id' => $row->product_id,
                'name' => $this->nameOf($row),
                'quantity' => (int) $row->quantity,
                'revenue_sar' => $this->money($row->revenue),
            ])
            ->all();
    }
}
