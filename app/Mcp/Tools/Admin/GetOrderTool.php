<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetOrderTool extends AdminTool
{
    protected function key(): string
    {
        return 'get_order';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_number' => $schema->string()->description($this->parameter('order_number'))->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $order = Order::query()
            ->with('items', 'store:id,name_ar,name_en,phone', 'user:id,name,phone,email', 'review')
            ->where('order_number', trim((string) $request->get('order_number')))
            ->first();

        if (! $order) {
            return $this->json(['found' => false, 'error' => 'No order with this number. Use search_orders to find it.']);
        }

        return $this->json([
            'found' => true,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'can_move_to' => $order->allowedStatusTransitions(),
            'customer' => [
                'user_id' => $order->user_id,
                'name' => $order->customer_name ?: $order->user?->name,
                'phone' => $order->customer_phone ?: $order->user?->phone,
                'email' => $order->user?->email,
            ],
            'delivery' => [
                'city' => $order->city,
                'address' => $order->delivery_address,
                'notes' => $order->notes,
                'courier' => array_filter([
                    'name' => $order->courier_name,
                    'phone' => $order->courier_phone,
                    'eta_minutes' => $order->courier_eta_minutes,
                ]) ?: null,
            ],
            'store' => ['id' => $order->store_id, 'name' => $this->nameOf($order->store), 'phone' => $order->store?->phone],
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'product_id' => $item->product_id,
                'name' => $this->names($item->product_name_ar, $item->product_name_en),
                'quantity' => $item->quantity,
                'unit_price_sar' => $this->money($item->unit_price),
            ])->all(),
            'totals_sar' => [
                'subtotal' => $this->money($order->subtotal),
                'vat' => $this->money($order->vat_amount),
                'delivery_fee' => $this->money($order->delivery_fee),
                'total' => $this->money($order->total),
            ],
            'payment' => ['method' => $order->payment_method, 'status' => $order->payment_status],
            'timeline' => array_filter([
                'ordered' => $order->created_at?->toDateTimeString(),
                'accepted' => $order->accepted_at?->toDateTimeString(),
                'prepared' => $order->prepared_at?->toDateTimeString(),
                'ready' => $order->ready_at?->toDateTimeString(),
                'out_for_delivery' => $order->out_for_delivery_at?->toDateTimeString(),
                'delivered' => $order->delivered_at?->toDateTimeString(),
                'cancelled' => $order->cancelled_at?->toDateTimeString(),
            ]),
            'review' => $order->review ? [
                'rating' => $order->review->rating,
                'comment' => $order->review->comment,
            ] : null,
        ]);
    }
}
