@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.products.title', ['name' => $store->name_ar])" />

    <x-admin::flash />

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin::card :title="__('admin.cards.total_products')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['total'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.common.active')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['active'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.cards.low_stock')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['low'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.cards.out_of_stock')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['out'] }}</p>
        </x-admin::card>
    </div>

    <x-admin::table-toolbar :search-action="route('admin.vendors.products.index', $vendor)"
        :search-placeholder="__('admin.search.products')" :filters="$filters"
        :create-url="route('admin.vendors.products.create', $vendor)"
        :create-label="__('admin.pages.products.create', ['name' => $store->name_ar])">
        <form method="GET" action="{{ route('admin.vendors.products.index', $vendor) }}" class="flex flex-wrap gap-2">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="category_id" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-44">
                <option value="">{{ __('admin.filters.all_categories') }}</option>
                @foreach ($categoryOptions as $categoryId => $categoryName)
                    <option value="{{ $categoryId }}" @selected((string) request('category_id') === (string) $categoryId)>
                        {{ $categoryName }}
                    </option>
                @endforeach
            </select>

            <select name="stock" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-40">
                <option value="">{{ __('admin.filters.all_stock_levels') }}</option>
                <option value="low" @selected(request('stock') === 'low')>{{ __('admin.filters.low') }}</option>
                <option value="out" @selected(request('stock') === 'out')>{{ __('admin.cards.out_of_stock') }}</option>
            </select>

            <select name="is_active" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-40">
                <option value="">{{ __('admin.filters.all_statuses') }}</option>
                <option value="1" @selected(request('is_active') === '1')>{{ __('admin.common.active') }}</option>
                <option value="0" @selected(request('is_active') === '0')>{{ __('admin.common.inactive') }}</option>
            </select>
        </form>
    </x-admin::table-toolbar>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.product') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.category') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.price') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.stock') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.sku') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    @if ($product->image)
                                        <img src="{{ api_image($product->image) }}" alt="{{ $product->name_ar }}"
                                            class="size-10 rounded-lg object-cover" />
                                    @endif
                                    <div>
                                        <p class="td-strong">{{ $product->name_ar }}</p>
                                        @if ($product->name_en)
                                            <p class="td-text">{{ $product->name_en }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $product->category?->name_ar ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-strong">{{ number_format((float) $product->price, 2) }}</p>
                                @if ($product->original_price)
                                    <p class="td-text line-through">{{ number_format((float) $product->original_price, 2) }}</p>
                                @endif
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$product->stock === 0 ? 'error' : 'light'">
                                    {{ $product->stock }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $product->sku ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$product->is_active ? 'success' : 'light'">
                                    {{ $product->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.vendors.products.edit', [$vendor, $product])"
                                    :delete-url="route('admin.vendors.products.destroy', [$vendor, $product])"
                                    :delete-message="__('admin.delete.product', ['name' => $product->name_ar])">
                                    <x-admin::status-toggle :action="route('admin.vendors.products.toggle', [$vendor, $product])"
                                        :active="$product->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin::empty-state :title="__('admin.empty.products')"
                                    :message="__('admin.messages.no_products')" icon="ecommerce">
                                    <a href="{{ route('admin.vendors.products.create', $vendor) }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.products.create', ['name' => $store->name_ar]) }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$products" />
    </div>
@endsection
