@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.users.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.users.index')" :search-placeholder="__('admin.search.users')"
        :filters="$filters" create-route="admin.users.create" :create-label="__('admin.pages.users.create')">
        <form method="GET" action="{{ route('admin.users.index') }}" class="w-full sm:w-40">
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
            <table class="w-full min-w-max text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.name') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.phone') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.email') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.city') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.orders') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.addresses') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-admin::avatar :src="$user->avatar ? api_image($user->avatar) : null"
                                        :alt="$user->name" :fallback="mb_substr($user->name, 0, 1)" size="sm" />
                                    <div>
                                        <p class="td-strong">{{ $user->name }}</p>
                                        <p class="td-text">{{ $user->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $user->phone ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $user->email ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $user->city?->name_ar ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" color="light">{{ $user->orders_count }}</x-admin::badge>
                            </td>
                            <td class="td">
                                <a href="{{ route('admin.users.addresses', $user) }}"
                                    class="text-brand-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-500/15 inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm font-medium transition">
                                    {{ $user->addresses_count }}
                                    <svg class="stroke-current rtl:rotate-180" width="14" height="14"
                                        viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$user->is_active ? 'success' : 'light'">
                                    {{ $user->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.users.edit', $user)"
                                    :delete-url="route('admin.users.destroy', $user)"
                                    :delete-message="__('admin.common.delete').' '.$user->name.'?'">
                                    <x-admin::status-toggle :action="route('admin.users.toggle', $user)"
                                        :active="$user->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin::empty-state :title="__('admin.empty.users')" icon="authentication">
                                    <a href="{{ route('admin.users.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.users.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$users" />
    </div>
@endsection
