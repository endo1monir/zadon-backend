<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

/**
 * Same rules as the admin panel's order page: only allowed next statuses,
 * and the customer gets a notification.
 */
#[IsDestructive]
class UpdateOrderStatusTool extends AdminAction
{
    protected function key(): string
    {
        return 'update_order_status';
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'order_number' => $schema->string()->description($this->parameter('order_number'))->required(),
            'status' => $schema->string()->enum(['preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'])->description($this->parameter('status'))->required(),
        ];
    }

    protected function prepare(Request $request): string|array
    {
        $order = Order::query()->where('order_number', trim((string) $request->get('order_number')))->first();
        $status = (string) $request->get('status');

        if (! $order) {
            return 'No order with this number. Use search_orders to find it.';
        }

        if (! $order->canTransitionTo($status)) {
            $allowed = $order->allowedStatusTransitions();

            return "Order {$order->order_number} is \"{$order->status}\" and cannot move to \"{$status}\". "
                .($allowed === [] ? 'It is finished and cannot change.' : 'It can move to: '.implode(', ', $allowed).'.');
        }

        $summary = "Order {$order->order_number} ({$order->customer_name}, {$order->total} SAR): {$order->status} → {$status}. The customer will be notified.";

        return [$summary, function () use ($order, $status): array {
            $order->setStatus($status);
            $order->notifyCustomerStatusChange($status === 'cancelled');

            return ['order_number' => $order->order_number, 'status' => $order->status];
        }];
    }
}
