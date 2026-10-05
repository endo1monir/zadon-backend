@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.orders.title')" />

    <x-admin::flash />

    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-admin::card :title="__('admin.cards.all_orders')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ array_sum($statusCounts) }}</p>
        </x-admin::card>
        @foreach ($statusOptions as $statusValue => $statusLabel)
            <x-admin::card :title="$statusLabel">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    {{ $statusCounts[$statusValue] ?? 0 }}</p>
            </x-admin::card>
        @endforeach
    </div>

    <x-admin::table-toolbar :search-action="route('admin.orders.index')"
        :search-placeholder="__('admin.search.orders')" :filters="$filters">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap gap-3">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <select name="status" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-44">
                <option value="">{{ __('admin.filters.all_statuses') }}</option>
                @foreach ($statusOptions as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(request('status') === $statusValue)>
                        {{ $statusLabel }}</option>
                @endforeach
            </select>

            <select name="payment_status" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-40">
                <option value="">{{ __('admin.filters.all_payments') }}</option>
                @foreach ($paymentStatusOptions as $paymentValue => $paymentLabel)
                    <option value="{{ $paymentValue }}" @selected(request('payment_status') === $paymentValue)>
                        {{ $paymentLabel }}</option>
                @endforeach
            </select>

            <select name="store_id" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-44">
                <option value="">{{ __('admin.filters.all_stores') }}</option>
                @foreach ($storeOptions as $storeId => $storeName)
                    <option value="{{ $storeId }}" @selected((string) request('store_id') === (string) $storeId)>
                        {{ $storeName }}</option>
                @endforeach
            </select>
        </form>
    </x-admin::table-toolbar>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.order') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.customer') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.store') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.items') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.total') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.payment') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.placed') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="table-row">
                            <td class="td">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="text-brand-500 hover:text-brand-600 font-medium">
                                    {{ $order->order_number }}
                                </a>
                                <p class="td-text">{{ $order->city ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <div class="flex items-center gap-2">
                                    <p class="td-strong">{{ $order->customer_name ?: '—' }}</p>
                                    @unless ($order->user_id)
                                        <x-admin::badge size="sm" color="light">{{ __('admin.common.guest_order') }}</x-admin::badge>
                                    @endunless
                                </div>
                                <p class="td-text">{{ $order->customer_phone ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $order->store?->name_ar ?? '—' }}</p>
                            </td>
                            <td class="td"><p class="td-text">{{ $order->items_count }}</p></td>
                            <td class="td">
                                <p class="td-strong">{{ number_format((float) $order->total, 2) }} {{ __('admin.common.currency') }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $paymentMethodOptions[$order->payment_method] ?? $order->payment_method }}</p>
                                <x-admin::badge size="sm" :color="$order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'error' : 'light')">
                                    {{ $paymentStatusOptions[$order->payment_status] ?? $order->payment_status }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="match ($order->status) {
                                    'delivered' => 'success',
                                    'cancelled' => 'error',
                                    'new' => 'info',
                                    default => 'warning',
                                }">{{ $statusOptions[$order->status] ?? $order->status }}</x-admin::badge>
                            </td>
                            <td class="td"><p class="td-text">{{ $order->created_at?->diffForHumans() }}</p></td>
                            <td class="td">
                                <x-admin::row-actions :show-url="route('admin.orders.show', $order)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-admin::empty-state :title="__('admin.empty.orders')"
                                    :message="__('admin.messages.no_orders')" icon="ecommerce" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$orders" />
    </div>
@endsection
