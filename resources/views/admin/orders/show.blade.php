@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.orders.title')">
        <a href="{{ route('admin.orders.index') }}"
            class="bg-white text-gray-700 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium ring-1 ring-inset transition">
            {{ __('admin.text.back_to_list') }}
        </a>
    </x-admin::page-header>

    <x-admin::flash />

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-admin::card :title="__('admin.th.order')">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-admin::detail :label="__('admin.text.labels.order_number')">
                        <span class="font-medium">{{ $order->order_number }}</span>
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.th.status')">
                        <x-admin::badge size="sm" :color="match ($order->status) {
                            'delivered' => 'success',
                            'cancelled' => 'error',
                            'new' => 'info',
                            default => 'warning',
                        }">{{ $statusOptions[$order->status] ?? $order->status }}</x-admin::badge>
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.text.labels.customer')">
                        <div class="flex items-center gap-2">
                            <span>{{ $order->customer_name ?: '—' }}</span>
                            @unless ($order->user_id)
                                <x-admin::badge size="sm" color="light">{{ __('admin.common.guest_order') }}</x-admin::badge>
                            @endunless
                        </div>
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.common.phone')">{{ $order->customer_phone ?: '—' }}</x-admin::detail>

                    <x-admin::detail :label="__('admin.common.email')">{{ $order->user?->email ?: '—' }}</x-admin::detail>

                    <x-admin::detail :label="__('admin.common.city')">{{ $order->city ?: '—' }}</x-admin::detail>

                    <x-admin::detail :label="__('admin.text.labels.delivery_address')" :colspan="2">
                        {{ $order->delivery_address ?: '—' }}
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.common.notes')" :colspan="2">{{ $order->notes ?: '—' }}</x-admin::detail>

                    <x-admin::detail :label="__('admin.th.placed')" :colspan="2">
                        {{ $order->created_at?->format('Y-m-d H:i') ?: '—' }}
                    </x-admin::detail>
                </dl>
            </x-admin::card>

            <x-admin::card :title="__('admin.th.items')">
                <div class="table-shell">
                    <div class="table-scroll">
                        <table class="w-full min-w-max text-start">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="th"><p class="th-label">{{ __('admin.th.product') }}</p></th>
                                    <th class="th"><p class="th-label">{{ __('admin.th.unit_price') }}</p></th>
                                    <th class="th"><p class="th-label">{{ __('admin.th.qty') }}</p></th>
                                    <th class="th"><p class="th-label">{{ __('admin.th.packed') }}</p></th>
                                    <th class="th"><p class="th-label">{{ __('admin.th.line_total') }}</p></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->items as $item)
                                    <tr class="table-row">
                                        <td class="td">
                                            @if ($item->image)
                                                <img src="{{ api_image($item->image) }}" alt="{{ $item->product_name_ar }}"
                                                    class="size-10 rounded-lg object-cover" />
                                            @endif
                                            <p class="td-strong">{{ $item->product_name_ar }}</p>
                                            @if ($item->product_name_en)
                                                <p class="td-text">{{ $item->product_name_en }}</p>
                                            @endif
                                        </td>
                                        <td class="td">
                                            <p class="td-text">
                                                {{ number_format((float) $item->unit_price, 2) }}
                                                {{ __('admin.common.currency') }}
                                            </p>
                                        </td>
                                        <td class="td">
                                            <p class="td-text">{{ $item->quantity }} {{ $item->unit ?: '' }}</p>
                                        </td>
                                        <td class="td">
                                            <x-admin::badge size="sm" :color="$item->packed ? 'success' : 'light'">
                                                {{ $item->packed ? __('admin.text.labels.packed') : __('admin.text.labels.not_packed') }}
                                            </x-admin::badge>
                                        </td>
                                        <td class="td">
                                            <p class="td-strong">
                                                {{ number_format((float) $item->line_total, 2) }}
                                                {{ __('admin.common.currency') }}
                                            </p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-admin::empty-state :title="__('admin.empty.products')"
                                                :message="__('admin.messages.no_products')" icon="ecommerce" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-admin::card>

            <x-admin::card :title="__('admin.text.labels.totals')">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-admin::detail :label="__('admin.text.labels.subtotal')">
                        {{ number_format((float) $order->subtotal, 2) }} {{ __('admin.common.currency') }}
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.common.total')">
                        <span class="font-medium">{{ number_format((float) $order->total, 2) }} {{ __('admin.common.currency') }}</span>
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.form.labels.delivery_fee')">
                        {{ number_format((float) $order->delivery_fee, 2) }} {{ __('admin.common.currency') }}
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.text.labels.vat')">
                        {{ number_format((float) $order->vat_amount, 2) }} {{ __('admin.common.currency') }}
                    </x-admin::detail>
                </dl>
            </x-admin::card>
        </div>

        <div class="space-y-6">
            <x-admin::card :title="__('admin.text.labels.update_status')">
                @if ($nextStatuses)
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')

                        <x-admin::form.select name="status" :label="__('admin.common.status')" :options="$nextStatuses"
                            :value="$order->status" />

                        <button type="submit"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 mt-5 w-full rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                            {{ __('admin.text.labels.update_status') }}
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.text.notice.order_final_status') }}</p>
                @endif

                <dl class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-1">
                    <x-admin::detail :label="__('admin.text.labels.timeline')">
                        <ul class="space-y-2">
                            @foreach ([
                                'accepted_at' => __('admin.options.order_statuses.preparing'),
                                'prepared_at' => __('admin.options.order_statuses.ready_for_pickup'),
                                'ready_at' => __('admin.options.order_statuses.out_for_delivery'),
                                'out_for_delivery_at' => __('admin.options.order_statuses.out_for_delivery'),
                                'delivered_at' => __('admin.options.order_statuses.delivered'),
                                'cancelled_at' => __('admin.options.order_statuses.cancelled'),
                            ] as $field => $statusLabel)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $statusLabel }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $order->{$field}?->format('Y-m-d H:i') ?: '—' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </x-admin::detail>
                </dl>
            </x-admin::card>

            <x-admin::card :title="__('admin.text.labels.store')">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-1">
                    <x-admin::detail :label="__('admin.th.store')">{{ $order->store?->name_ar ?? '—' }}</x-admin::detail>

                    <x-admin::detail :label="__('admin.common.phone')">{{ $order->store?->phone ?: '—' }}</x-admin::detail>
                </dl>
            </x-admin::card>

            <x-admin::card :title="__('admin.th.payment')">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-1">
                    <x-admin::detail :label="__('admin.text.labels.payment_method')">
                        {{ $paymentMethodOptions[$order->payment_method] ?? $order->payment_method }}
                    </x-admin::detail>

                    <x-admin::detail :label="__('admin.text.labels.payment_status')">
                        <x-admin::badge size="sm" :color="match ($order->payment_status) {
                            'paid' => 'success',
                            'failed' => 'error',
                            'refunded' => 'info',
                            default => 'light',
                        }">{{ $paymentStatusOptions[$order->payment_status] ?? $order->payment_status }}</x-admin::badge>
                    </x-admin::detail>
                </dl>
            </x-admin::card>

            @if ($order->courier_name || $order->courier_phone || $order->courier_eta_minutes)
                <x-admin::card :title="__('admin.text.labels.courier')">
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-1">
                        <x-admin::detail :label="__('admin.th.name')">{{ $order->courier_name ?: '—' }}</x-admin::detail>

                        <x-admin::detail :label="__('admin.common.phone')">{{ $order->courier_phone ?: '—' }}</x-admin::detail>

                        <x-admin::detail :label="__('admin.text.labels.courier_eta')">
                            {{ $order->courier_eta_minutes ?: '—' }}
                        </x-admin::detail>
                    </dl>
                </x-admin::card>
            @endif

            @if ($order->review)
                <x-admin::card :title="__('admin.text.labels.review')">
                    <x-admin::rating :value="$order->review->rating" size="lg" />

                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $order->review->comment ?: '—' }}</p>

                    @if ($order->review->store_reply)
                        <p class="mt-3 text-sm text-gray-800 dark:text-white/90">{{ $order->review->store_reply }}</p>
                    @endif
                </x-admin::card>
            @endif
        </div>
    </div>
@endsection
