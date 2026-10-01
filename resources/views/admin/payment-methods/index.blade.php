@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Payment methods" />

    <x-admin::flash />

    <x-admin::table-toolbar search-action="{{ route('admin.payment-methods.index') }}"
        search-placeholder="Search payment methods..." :filters="$filters"
        create-route="admin.payment-methods.create" create-label="Add payment method">
        <form method="GET" action="{{ route('admin.payment-methods.index') }}" class="w-full sm:w-40">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="is_active" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">All statuses</option>
                <option value="1" @selected(request('is_active') === '1')>Active</option>
                <option value="0" @selected(request('is_active') === '0')>Inactive</option>
            </select>
        </form>
    </x-admin::table-toolbar>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">Icon</p></th>
                        <th class="th"><p class="th-label">Name</p></th>
                        <th class="th"><p class="th-label">Key</p></th>
                        <th class="th"><p class="th-label">Sort</p></th>
                        <th class="th"><p class="th-label">Status</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
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
                                    {{ $paymentMethod->is_active ? 'Active' : 'Inactive' }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.payment-methods.edit', $paymentMethod)"
                                    :delete-url="route('admin.payment-methods.destroy', $paymentMethod)"
                                    :delete-message="'Delete the payment method &quot;'.$paymentMethod->name_en.'&quot;?'">
                                    <x-admin::status-toggle :action="route('admin.payment-methods.toggle', $paymentMethod)"
                                        :active="$paymentMethod->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin::empty-state title="No payment methods found"
                                    message="Add the payment methods the app should offer at checkout." icon="ecommerce">
                                    <a href="{{ route('admin.payment-methods.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Add payment method</a>
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
