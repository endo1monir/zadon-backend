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
            'store' => __('admin.options.category_types.store'),
            'product' => __('admin.options.category_types.product'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function storeStatuses(): array
    {
        return [
            'open' => __('admin.options.store_statuses.open'),
            'busy' => __('admin.options.store_statuses.busy'),
            'closed' => __('admin.options.store_statuses.closed'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function orderStatuses(): array
    {
        return [
            'new' => __('admin.options.order_statuses.new'),
            'preparing' => __('admin.options.order_statuses.preparing'),
            'ready_for_pickup' => __('admin.options.order_statuses.ready_for_pickup'),
            'out_for_delivery' => __('admin.options.order_statuses.out_for_delivery'),
            'delivered' => __('admin.options.order_statuses.delivered'),
            'cancelled' => __('admin.options.order_statuses.cancelled'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function orderPaymentStatuses(): array
    {
        return [
            'pending' => __('admin.options.order_payment_statuses.pending'),
            'paid' => __('admin.options.order_payment_statuses.paid'),
            'failed' => __('admin.options.order_payment_statuses.failed'),
            'refunded' => __('admin.options.order_payment_statuses.refunded'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function userRoles(): array
    {
        return [
            'customer' => __('admin.options.user_roles.customer'),
            'vendor' => __('admin.options.user_roles.vendor'),
            'admin' => __('admin.options.user_roles.admin'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function productStorageTemps(): array
    {
        return [
            'ambient' => __('admin.options.product_storage_temps.ambient'),
            'chilled' => __('admin.options.product_storage_temps.chilled'),
            'frozen' => __('admin.options.product_storage_temps.frozen'),
        ];
    }

    /**
     * The ratings a review can carry, keyed by value.
     *
     * @return array<int, string>
     */
    public static function reviewRatings(): array
    {
        return [
            5 => trans_choice('admin.cards.stars', 5, ['count' => 5]),
            4 => trans_choice('admin.cards.stars', 4, ['count' => 4]),
            3 => trans_choice('admin.cards.stars', 3, ['count' => 3]),
            2 => trans_choice('admin.cards.stars', 2, ['count' => 2]),
            1 => trans_choice('admin.cards.stars', 1, ['count' => 1]),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function notificationTypes(): array
    {
        return [
            'order' => __('admin.options.notification_types.order'),
            'inventory' => __('admin.options.notification_types.inventory'),
            'system' => __('admin.options.notification_types.system'),
            'alert' => __('admin.options.notification_types.alert'),
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
            'cash' => __('admin.options.order_payment_methods.cash'),
            'card' => __('admin.options.order_payment_methods.card'),
            'apple_pay' => __('admin.options.order_payment_methods.apple_pay'),
            'wallet' => __('admin.options.order_payment_methods.wallet'),
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
