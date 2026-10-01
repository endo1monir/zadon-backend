@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Dashboard" />

    <x-admin::flash />

    <!-- Metrics -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
        @foreach ([
            ['label' => 'Users', 'value' => $stats['users'], 'growth' => $growth['users'], 'icon' => 'user-profile', 'class' => 'bg-brand-50 text-brand-500 dark:bg-brand-500/15'],
            ['label' => 'Stores', 'value' => $stats['stores'], 'growth' => $growth['stores'], 'icon' => 'ecommerce', 'class' => 'bg-success-50 text-success-600 dark:bg-success-500/15'],
            ['label' => 'Products', 'value' => $stats['products'], 'growth' => $growth['products'], 'icon' => 'task', 'class' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15'],
            ['label' => 'Orders', 'value' => $stats['orders'], 'growth' => $growth['orders'], 'icon' => 'calendar', 'class' => 'bg-error-50 text-error-600 dark:bg-error-500/15'],
        ] as $metric)
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</h3>
                    <span class="flex size-11 items-center justify-center rounded-xl {{ $metric['class'] }}">
                        {!! \App\Support\AdminIcons::svg($metric['icon']) !!}
                    </span>
                </div>
                <p class="text-title-md mt-3 font-semibold text-gray-800 dark:text-white/90">{{ number_format($metric['value']) }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs">
                    <span @class([
                        'font-medium',
                        'text-success-600 dark:text-success-500' => $metric['growth'] >= 0,
                        'text-error-600 dark:text-error-500' => $metric['growth'] < 0,
                    ])>
                        {{ number_format(abs($metric['growth']), 1) }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">last 30 days</span>
                </p>
            </div>
        @endforeach
    </div>

    <!-- Revenue + pending orders -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:col-span-2 2xl:col-span-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Orders (last 30 days)</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daily order volume.</p>
                </div>
            </div>

            <div id="ordersChart" class="mt-5 h-64 w-full"></div>
        </div>

        <div class="flex flex-col gap-4 sm:col-span-2 2xl:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <span
                    class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 flex size-11 items-center justify-center rounded-xl">
                    {!! \App\Support\AdminIcons::svg('ecommerce') !!}
                </span>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Total revenue</p>
                <p class="text-title-md mt-1 font-semibold text-gray-800 dark:text-white/90">
                    {{ number_format($stats['revenue'], 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <span
                    class="bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500 flex size-11 items-center justify-center rounded-xl">
                    {!! \App\Support\AdminIcons::svg('chat') !!}
                </span>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">New orders</p>
                <p class="text-title-md mt-1 font-semibold text-gray-800 dark:text-white/90">
                    {{ number_format($stats['pending_orders']) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Recent orders -->
    <div class="mt-6">
        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Recent orders</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-max text-left">
                    <thead>
                        <tr>
                            <th
                                class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                                Order
                            </th>
                            <th
                                class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                                Customer
                            </th>
                            <th
                                class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                                Store
                            </th>
                            <th
                                class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                                Status
                            </th>
                            <th
                                class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($recentOrders as $order)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $order->order_number }}
                                    <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                                        {{ $order->created_at?->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $order->user?->name ?? $order->customer_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $order->store?->name_en ?? 'â€”' }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-admin::status-badge :status="$order->status" />
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ number_format((float) $order->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin::empty-state title="No orders yet" icon="calendar" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top stores + low stock -->
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Top stores</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($topStores as $store)
                    <div class="flex items-center gap-4 px-6 py-4">
                        <x-admin::avatar :src="api_image($store->logo)" :alt="$store->name_en"
                            :fallback="str($store->name_en)->substr(0, 1)" size="md" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $store->name_en }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $store->products_count }} {{ str('product')->plural($store->products_count) }}
                            </p>
                        </div>
                        <x-admin::badge :color="$store->is_active ? 'success' : 'light'" variant="light" size="sm">
                            {{ $store->is_active ? 'Active' : 'Inactive' }}
                        </x-admin::badge>
                    </div>
                @empty
                    <x-admin::empty-state title="No stores yet" icon="ecommerce" />
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Low stock products</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center gap-4 px-6 py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $product->name_en }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format((float) $product->price, 2) }}
                            </p>
                        </div>
                        <x-admin::badge :color="$product->stock <= 0 ? 'error' : 'warning'" variant="light"
                            size="sm">
                            {{ $product->stock <= 0 ? 'Out of stock' : $product->stock . ' left' }}
                        </x-admin::badge>
                    </div>
                @empty
                    <x-admin::empty-state title="No products yet" icon="task" />
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            window.renderChart?.('#ordersChart', {
                chart: {
                    type: 'area',
                    height: 256,
                    toolbar: { show: false },
                    fontFamily: 'Outfit, sans-serif',
                    zoom: { enabled: false },
                },
                series: [{
                    name: 'Orders',
                    data: @json($ordersChart['series']),
                }],
                xaxis: {
                    categories: @json($ordersChart['labels']),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { labels: { formatter: (value) => Math.round(value) } },
                grid: { borderColor: '#e4e7ec', strokeDashArray: 5 },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#465fff'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.05,
                        stops: [0, 95, 100],
                    },
                },
                legend: { show: false },
            });
        });
    </script>
@endpush
