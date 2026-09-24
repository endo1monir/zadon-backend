<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\OrderStatusRequest;
use App\Http\Requests\Api\Vendor\SetPackedRequest;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $orders = $this->managedStore()->orders()
            ->with('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('order_number', 'like', "%{$request->string('search')}%")
                    ->orWhere('customer_name', 'like', "%{$request->string('search')}%")
                    ->orWhere('customer_phone', 'like', "%{$request->string('search')}%"))
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusCounts = $this->managedStore()->orders()
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return $this->successReturn([
            'orders' => OrderResource::collection($orders),
            'status_counts' => $statusCounts,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order = $this->managedStore()->orders()->with('items', 'store')->findOrFail($order->id);

        return $this->successReturn([
            'order' => new OrderResource($order),
        ]);
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->managedStore()->orders()->findOrFail($order->id);

        $flow = [
            'new' => ['preparing'],
            'preparing' => ['ready_for_pickup'],
            'ready_for_pickup' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
        ];

        if (! in_array($request->status, $flow[$order->status] ?? [], true)) {
            return $this->failReturn('messages.invalid_status_transition');
        }

        $order->setStatus($request->status);
        $this->notifyCustomer($order);

        return $this->successReturn([
            'order' => new OrderResource($order->fresh()->load('items', 'store')),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $order = $this->managedStore()->orders()->findOrFail($order->id);

        if (in_array($order->status, ['delivered', 'cancelled'], true)) {
            return $this->failReturn('messages.order_cannot_be_cancelled');
        }

        $order->setStatus('cancelled');

        if ($order->payment_method === 'wallet' && $order->payment_status === 'paid') {
            $this->refundWallet($order);
        }

        $this->notifyCustomer($order, cancelled: true);

        return $this->successReturn([
            'order' => new OrderResource($order->fresh()->load('items', 'store')),
        ]);
    }

    public function setPacked(SetPackedRequest $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $orderItem = $this->managedStore()->orders()
            ->findOrFail($order->id)
            ->items()
            ->findOrFail($orderItem->id);

        $orderItem->update(['packed' => $request->boolean('packed')]);

        return $this->successReturn([
            'item' => new OrderItemResource($orderItem),
        ]);
    }

    private function notifyCustomer(Order $order, bool $cancelled = false): void
    {
        if (! $order->user_id) {
            return;
        }

        $message = $cancelled
            ? ['title_ar' => 'تم إلغاء طلبك', 'title_en' => 'Your order was cancelled']
            : ['title_ar' => "حدث جديد على طلبك {$order->order_number}", 'title_en' => "Your order {$order->order_number} was updated"];

        $order->user->notifications()->create($message + [
            'type' => 'order',
            'order_id' => $order->id,
            'action_label_ar' => 'متابعة الطلب',
            'action_label_en' => 'Track order',
        ]);
    }

    private function refundWallet(Order $order): void
    {
        $wallet = $order->user->wallet()->firstOrCreate([]);
        $balance = (float) $wallet->balance + (float) $order->total;

        $wallet->balance = $balance;
        $wallet->save();

        $wallet->transactions()->create([
            'user_id' => $order->user_id,
            'type' => 'credit',
            'amount' => $order->total,
            'balance_after' => $balance,
            'description' => trans('messages.refund_order', ['number' => $order->order_number]),
        ]);
    }
}
