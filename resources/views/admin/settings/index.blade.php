@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.settings.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.settings.index')"
        :search-placeholder="__('admin.search.settings')" :filters="$filters" create-route="admin.settings.create"
        :create-label="__('admin.pages.settings.create')" />

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.key') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.value_preview') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
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
                                    :delete-message="__('admin.delete.setting', ['name' => $setting->key])" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-admin::empty-state :title="__('admin.empty.settings')"
                                    :message="__('admin.messages.no_settings')" icon="tables">
                                    <a href="{{ route('admin.settings.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.settings.create') }}</a>
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
