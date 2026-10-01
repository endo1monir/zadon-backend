@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Categories" />

    <x-admin::flash />

    <x-admin::table-toolbar search-action="{{ route('admin.categories.index') }}"
        search-placeholder="Search categories..." :filters="$filters" create-route="admin.categories.create"
        create-label="Add category">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-wrap gap-3">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if (request()->filled('is_active'))
                <input type="hidden" name="is_active" value="{{ request('is_active') }}">
            @endif

            <select name="type" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-44">
                <option value="">All types</option>
                @foreach (\App\Support\AdminOptions::categoryTypes() as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="is_active" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-36">
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
                        <th class="th"><p class="th-label">Name</p></th>
                        <th class="th"><p class="th-label">Type</p></th>
                        <th class="th"><p class="th-label">Slug</p></th>
                        <th class="th"><p class="th-label">Parent</p></th>
                        <th class="th"><p class="th-label">Usage</p></th>
                        <th class="th"><p class="th-label">Sort</p></th>
                        <th class="th"><p class="th-label">Status</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="table-row">
                            <td class="td">
                                <p class="td-strong">{{ $category->name_ar }}</p>
                                <p class="td-text">{{ $category->name_en ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$category->type === 'store' ? 'primary' : 'info'">
                                    {{ \App\Support\AdminOptions::categoryTypes()[$category->type] ?? $category->type }}
                                </x-admin::badge>
                            </td>
                            <td class="td"><p class="td-text font-mono text-xs">{{ $category->slug }}</p></td>
                            <td class="td">
                                <p class="td-text">{{ $category->parent?->name_ar ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <div class="flex flex-wrap gap-1.5">
                                    <x-admin::badge size="sm" color="light">Stores: {{ $category->stores_count }}</x-admin::badge>
                                    <x-admin::badge size="sm" color="light">Products: {{ $category->products_count }}</x-admin::badge>
                                </div>
                            </td>
                            <td class="td"><p class="td-text">{{ $category->sort_order }}</p></td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$category->is_active ? 'success' : 'light'">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.categories.edit', $category)"
                                    :delete-url="route('admin.categories.destroy', $category)"
                                    :delete-message="'Delete the category &quot;'.$category->name_ar.'&quot;?'">
                                    <x-admin::status-toggle :action="route('admin.categories.toggle', $category)"
                                        :active="$category->is_active" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin::empty-state title="No categories found"
                                    message="Categories are used to group both stores and products." icon="tables">
                                    <a href="{{ route('admin.categories.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Add category</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$categories" />
    </div>
@endsection
