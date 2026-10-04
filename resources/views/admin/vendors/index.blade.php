@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Vendors" />

    <x-admin::flash />

    <x-admin::table-toolbar search-action="{{ route('admin.vendors.index') }}" search-placeholder="Search vendors..."
        :filters="$filters" create-route="admin.vendors.create" create-label="Add vendor">
        <form method="GET" action="{{ route('admin.vendors.index') }}" class="w-full sm:w-40">
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
                        <th class="th"><p class="th-label">Owner</p></th>
                        <th class="th"><p class="th-label">Store</p></th>
                        <th class="th"><p class="th-label">Category</p></th>
                        <th class="th"><p class="th-label">City</p></th>
                        <th class="th"><p class="th-label">Store status</p></th>
                        <th class="th"><p class="th-label">Verified</p></th>
                        <th class="th"><p class="th-label">Products</p></th>
                        <th class="th"><p class="th-label">Account</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        @php($managedStore = $vendor->stores->first())
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-admin::avatar :src="$vendor->avatar ? api_image($vendor->avatar) : null"
                                        :alt="$vendor->name" :fallback="mb_substr($vendor->name, 0, 1)" size="sm" />
                                    <div>
                                        <p class="td-strong">{{ $vendor->name }}</p>
                                        <p class="td-text">{{ $vendor->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                @if ($managedStore)
                                    <p class="td-strong">{{ $managedStore->name_ar }}</p>
                                    <p class="td-text">{{ $managedStore->name_en ?: '—' }}</p>
                                @else
                                    <p class="td-text">No store</p>
                                @endif
                            </td>
                            <td class="td"><p class="td-text">{{ $managedStore?->category?->name_ar ?? '—' }}</p></td>
                            <td class="td"><p class="td-text">{{ $managedStore?->city?->name_ar ?? $vendor->city?->name_ar ?? '—' }}</p></td>
                            <td class="td">
                                @if ($managedStore)
                                    <x-admin::badge size="sm"
                                        :color="match ($managedStore->status) {
                                            'open' => 'success',
                                            'busy' => 'warning',
                                            default => 'light',
                                        }">{{ ucfirst($managedStore->status) }}</x-admin::badge>
                                @else
                                    <p class="td-text">—</p>
                                @endif
                            </td>
                            <td class="td">
                                @if ($managedStore)
                                    <x-admin::badge size="sm" :color="$managedStore->is_verified ? 'success' : 'warning'">
                                        {{ $managedStore->is_verified ? 'Verified' : 'Unverified' }}
                                    </x-admin::badge>
                                @else
                                    <p class="td-text">—</p>
                                @endif
                            </td>
                            <td class="td">
                                @if ($managedStore)
                                    <a href="{{ route('admin.vendors.products.index', $vendor) }}"
                                        class="text-brand-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-500/15 inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm font-medium transition">
                                        {{ $managedStore->products_count }}
                                        <svg class="stroke-current rtl:rotate-180" width="14" height="14"
                                            viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                                stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                @else
                                    <p class="td-text">—</p>
                                @endif
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$vendor->is_active ? 'success' : 'light'">
                                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.vendors.edit', $vendor)"
                                    :delete-url="route('admin.vendors.destroy', $vendor)"
                                    :delete-message="'Delete the vendor &quot;'.$vendor->name.'&quot; and its stores?'">
                                    <x-admin::status-toggle :action="route('admin.vendors.toggle', $vendor)" :active="$vendor->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-admin::empty-state title="No vendors found"
                                    message="Vendors sign in to the store dashboard with their phone and password."
                                    icon="authentication">
                                    <a href="{{ route('admin.vendors.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Add vendor</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$vendors" />
    </div>
@endsection
