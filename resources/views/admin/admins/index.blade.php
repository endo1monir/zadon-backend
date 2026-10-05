@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.admins.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.admins.index')" :search-placeholder="__('admin.search.admins')"
        :filters="$filters" create-route="admin.admins.create" :create-label="__('admin.pages.admins.create')">
        <form method="GET" action="{{ route('admin.admins.index') }}" class="w-full sm:w-40">
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
                        <th class="th"><p class="th-label">{{ __('admin.th.name') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.phone') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.email') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.city') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr class="table-row">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-admin::avatar :src="$admin->avatar ? api_image($admin->avatar) : null"
                                        :alt="$admin->name" :fallback="mb_substr($admin->name, 0, 1)" size="sm" />
                                    <div>
                                        <p class="td-strong">{{ $admin->name }}</p>
                                        <p class="td-text">{{ $admin->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $admin->phone ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $admin->email ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $admin->city?->name_ar ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$admin->is_active ? 'success' : 'light'">
                                    {{ $admin->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.admins.edit', $admin)"
                                    :delete-url="route('admin.admins.destroy', $admin)"
                                    :delete-message="__('admin.delete.admin', ['name' => $admin->name])">
                                    <x-admin::status-toggle :action="route('admin.admins.toggle', $admin)"
                                        :active="$admin->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin::empty-state :title="__('admin.empty.admins')"
                                    :message="__('admin.messages.no_admins')" icon="authentication">
                                    <a href="{{ route('admin.admins.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.admins.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$admins" />
    </div>
@endsection
