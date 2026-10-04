<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('store:id,name_ar,name_en')
            ->withCount('items')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('order_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term);
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('store_id'), fn ($query) => $query->where('store_id', $request->integer('store_id')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'store_id']),
            'statusOptions' => AdminOptions::orderStatuses(),
            'paymentMethodOptions' => AdminOptions::orderPaymentMethods(),
            'paymentStatusOptions' => AdminOptions::orderPaymentStatuses(),
            'storeOptions' => AdminOptions::stores(),
            'statusCounts' => Order::query()
                ->select('status')
                ->selectRaw('count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'items',
            'store:id,name_ar,name_en,phone',
            'user:id,name,phone,email',
            'paymentMethod',
            'review',
            'deliveryAddress',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'statusOptions' => AdminOptions::orderStatuses(),
            'paymentMethodOptions' => AdminOptions::orderPaymentMethods(),
            'paymentStatusOptions' => AdminOptions::orderPaymentStatuses(),
            'nextStatuses' => $order->allowedStatusTransitions(),
        ]);
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $status = $request->string('status')->toString();

        if (! $order->canTransitionTo($status)) {
            return back()->with('error', 'An order in the '.$order->status.' status cannot move to '.$status.'.');
        }

        $order->setStatus($status);
        $order->notifyCustomerStatusChange($status === 'cancelled');

        return back()->with('success', 'Order status updated to '.str_replace('_', ' ', $status).'.');
    }
}
