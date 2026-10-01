@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Cities" />

    <x-admin::flash />

    <x-admin::table-toolbar search-action="{{ route('admin.cities.index') }}" search-placeholder="Search cities..."
        :filters="$filters" create-route="admin.cities.create" create-label="Add city">
        <form method="GET" action="{{ route('admin.cities.index') }}" class="w-full sm:w-40">
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
                        <th class="th"><p class="th-label">Name (AR)</p></th>
                        <th class="th"><p class="th-label">Name (EN)</p></th>
                        <th class="th"><p class="th-label">Sort order</p></th>
                        <th class="th"><p class="th-label">Status</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
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
                                    {{ $city->is_active ? 'Active' : 'Inactive' }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.cities.edit', $city)"
                                    :delete-url="route('admin.cities.destroy', $city)"
                                    :delete-message="'Delete the city &quot;'.$city->name_ar.'&quot;?'">
                                    <x-admin::status-toggle :action="route('admin.cities.toggle', $city)"
                                        :active="$city->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin::empty-state title="No cities found"
                                    message="Add your first city so stores and customers can be assigned to it." icon="pages">
                                    <a href="{{ route('admin.cities.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Add city</a>
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
