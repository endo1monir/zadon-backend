@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.cities.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.cities.index')"
        :search-placeholder="__('admin.search.cities')" :filters="$filters" create-route="admin.cities.create"
        :create-label="__('admin.pages.cities.create')">
        <form method="GET" action="{{ route('admin.cities.index') }}" class="w-full sm:w-40">
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
                        <th class="th"><p class="th-label">{{ __('admin.th.name_ar') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.name_en') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.sort_order') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.status') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $city)
                        <tr class="table-row">
                            <td class="td"><p class="td-strong">{{ $city->name_ar }}</p></td>
                            <td class="td"><p class="td-text">{{ $city->name_en ?: '—' }}</p></td>
                            <td class="td"><p class="td-text">{{ $city->sort_order }}</p></td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$city->is_active ? 'success' : 'light'">
                                    {{ $city->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.cities.edit', $city)"
                                    :delete-url="route('admin.cities.destroy', $city)"
                                    :delete-message="__('admin.delete.city', ['name' => $city->name_ar])">
                                    <x-admin::status-toggle :action="route('admin.cities.toggle', $city)"
                                        :active="$city->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin::empty-state :title="__('admin.empty.cities')"
                                    :message="__('admin.messages.no_cities')" icon="pages">
                                    <a href="{{ route('admin.cities.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.cities.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$cities" />
    </div>
@endsection
