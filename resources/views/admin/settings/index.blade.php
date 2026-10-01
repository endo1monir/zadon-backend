@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Settings" />

    <x-admin::flash />

    <x-admin::table-toolbar search-action="{{ route('admin.settings.index') }}"
        search-placeholder="Search settings..." :filters="$filters" create-route="admin.settings.create"
        create-label="Add setting" />

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">Key</p></th>
                        <th class="th"><p class="th-label">Value preview</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                        <tr class="table-row">
                            <td class="td"><p class="td-strong font-mono text-xs">{{ $setting->key }}</p></td>
                            <td class="td">
                                <p class="td-text line-clamp-2 max-w-xl whitespace-normal">
                                    {{ \Illuminate\Support\Str::limit($setting->value, 160) }}</p>
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.settings.edit', $setting)"
                                    :delete-url="route('admin.settings.destroy', $setting)"
                                    :delete-message="'Delete the setting &quot;'.$setting->key.'&quot;?'" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-admin::empty-state title="No settings found"
                                    message="Settings are simple key and value pairs used across the app." icon="tables">
                                    <a href="{{ route('admin.settings.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Add setting</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$settings" />
    </div>
@endsection
