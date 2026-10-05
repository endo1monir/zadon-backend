@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.payment_methods.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.payment-methods.index')"
        :search-placeholder="__('admin.search.payment_methods')" :filters="$filters"
        create-route="admin.payment-methods.create" :create-label="__('admin.pages.payment_methods.create')">
        <form method="GET" action="{{ route('admin.payment-methods.index') }}" class="w-full sm:w-40">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="is_active" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
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
                        <th class="th"><p class="th-label">{{ __('admin.th.icon') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.name') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.key') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.sort') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentMethods as $paymentMethod)
                        <tr class="table-row">
                            <td class="td">
                                @if ($paymentMethod->icon)
                                    <img src="{{ api_image($paymentMethod->icon) }}" alt="{{ $paymentMethod->name_en }}"
                                        class="size-10 rounded-lg object-contain" />
                                @else
                                    <div
                                        class="bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500 flex size-10 items-center justify-center rounded-lg">
                                        {!! \App\Support\AdminIcons::svg('pages') !!}
                                    </div>
                                @endif
                            </td>
                            <td class="td">
                                <p class="td-strong">{{ $paymentMethod->name_ar }}</p>
                                <p class="td-text">{{ $paymentMethod->name_en }}</p>
                            </td>
                            <td class="td"><p class="td-text font-mono text-xs">{{ $paymentMethod->key }}</p></td>
                            <td class="td"><p class="td-text">{{ $paymentMethod->sort_order }}</p></td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$paymentMethod->is_active ? 'success' : 'light'">
                                    {{ $paymentMethod->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.payment-methods.edit', $paymentMethod)"
                                    :delete-url="route('admin.payment-methods.destroy', $paymentMethod)"
                                    :delete-message="__('admin.delete.payment_method', ['name' => $paymentMethod->name_en])">
                                    <x-admin::status-toggle :action="route('admin.payment-methods.toggle', $paymentMethod)"
                                        :active="$paymentMethod->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin::empty-state :title="__('admin.empty.payment_methods')"
                                    :message="__('admin.messages.no_payment_methods')" icon="ecommerce">
                                    <a href="{{ route('admin.payment-methods.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.payment_methods.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$paymentMethods" />
    </div>
@endsection
