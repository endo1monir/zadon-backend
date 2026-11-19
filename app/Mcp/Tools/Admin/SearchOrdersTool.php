<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SearchOrdersTool extends AdminTool
{
    protected function key(): string
    {
        return 'search_orders';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description($this->parameter('search')),
            'status' => $schema->string()->enum(['new', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'])->description($this->parameter('status')),
            'store_id' => $schema->integer()->description($this->parameter('store_id')),
            'from' => $schema->string()->description($this->parameter('from')),
            'to' => $schema->string()->description($this->parameter('to')),
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Order::query()
            ->with('store:id,name_ar,name_en')
            ->withCount('items')
            ->when(filled($request->get('search')), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->get('search')).'%';

                $query->where(fn ($query) => $query->where('order_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term));
            })
            ->when(filled($request->get('status')), fn ($query) => $query->where('status', $request->get('status')))
            ->when(filled($request->get('store_id')), fn ($query) => $query->where('store_id', (int) $request->get('store_id')))
            ->when(filled($request->get('from')) || filled($request->get('to')), fn ($query) => $query->whereBetween('created_at', $this->period($request)));

        $total = (clone $query)->count();

        return $this->json([
            'found' => $total,
            'orders' => $query->latest('id')->limit($this->limit($request))->get()->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_sar' => $this->money($order->total),
                'payment' => "{$order->payment_method} ({$order->payment_status})",
                'customer' => trim("{$order->customer_name} {$order->customer_phone}"),
                'store' => $this->nameOf($order->store),
                'items' => $order->items_count,
                'ordered_at' => $order->created_at?->toDateTimeString(),
            ])->all(),
        ]);
    }
}
