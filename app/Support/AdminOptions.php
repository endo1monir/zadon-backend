<?php

namespace App\Support;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;

class AdminOptions
{
    /**
     * @return array<int, string>
     */
    public static function categoryTypes(): array
    {
        return [
            'store' => 'Store category',
            'product' => 'Product category',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function storeStatuses(): array
    {
        return [
            'open' => 'Open',
            'busy' => 'Busy',
            'closed' => 'Closed',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function orderStatuses(): array
    {
        return [
            'new' => 'New',
            'preparing' => 'Preparing',
            'ready_for_pickup' => 'Ready for pickup',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function orderPaymentStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function userRoles(): array
    {
        return [
            'customer' => 'Customer',
            'store_owner' => 'Store owner',
            'admin' => 'Admin',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function productStorageTemps(): array
    {
        return [
            'ambient' => 'Ambient',
            'chilled' => 'Chilled',
            'frozen' => 'Frozen',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function notificationTypes(): array
    {
        return [
            'order' => 'Order',
            'inventory' => 'Inventory',
            'system' => 'System',
            'alert' => 'Alert',
        ];
    }

    /**
     * Payment method keys stored on `orders.payment_method`.
     *
     * @return array<int, string>
     */
    public static function orderPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'apple_pay' => 'Apple Pay',
            'wallet' => 'Wallet',
        ];
    }

    /**
     * Cities keyed by id, ordered by their configured sort order.
     *
     * @return array<int, string>
     */
    public static function cities(bool $activeOnly = false): array
    {
        return City::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->pluck('name_ar', 'id')
            ->all();
    }

    /**
     * Categories keyed by id, optionally filtered by type.
     *
     * @return array<int, string>
     */
    public static function categories(?string $type = null, bool $activeOnly = false, ?int $ignoreId = null): array
    {
        return Category::query()
            ->when($type, fn ($query) => $query->type($type))
            ->when($activeOnly, fn ($query) => $query->active())
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->pluck('name_ar', 'id')
            ->all();
    }

    /**
     * Stores keyed by id.
     *
     * @return array<int, string>
     */
    public static function stores(bool $activeOnly = false): array
    {
        return Store::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->orderBy('name_ar')
            ->pluck('name_ar', 'id')
            ->all();
    }

    /**
     * Users keyed by id, labelled with their name or phone.
     *
     * @return array<int, string>
     */
    public static function users(?string $role = null, bool $excludeAdmins = false): array
    {
        return User::query()
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($excludeAdmins, fn ($query) => $query->where('role', '!=', 'admin'))
            ->orderBy('name')
            ->get(['id', 'name', 'phone'])
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->name ?: ($user->phone ?: ('User #'.$user->id)),
            ])
            ->all();
    }

    /**
     * A compact list of recent orders keyed by id, for linking records to an order.
     *
     * @return array<int, string>
     */
    public static function recentOrders(int $limit = 200): array
    {
        return Order::query()
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'order_number', 'status'])
            ->mapWithKeys(fn (Order $order): array => [
                $order->id => $order->order_number.' — '.str_replace('_', ' ', $order->status),
            ])
            ->all();
    }
}
