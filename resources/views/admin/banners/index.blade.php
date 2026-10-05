@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.banners.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar create-route="admin.banners.create"
        :create-label="__('admin.pages.banners.create')" />

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.preview') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.file') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.uploaded') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr class="table-row">
                            <td class="td">
                                <img src="{{ api_image($banner->image) }}" alt="{{ __('admin.pages.banners.title') }}"
                                    class="h-14 w-32 rounded-lg object-cover" />
                            </td>
                            <td class="td"><p class="td-text font-mono text-xs">{{ $banner->image }}</p></td>
                            <td class="td"><p class="td-text">{{ $banner->created_at?->diffForHumans() }}</p></td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.banners.edit', $banner)"
                                    :delete-url="route('admin.banners.destroy', $banner)"
                                    :delete-message="__('admin.delete.banner', ['name' => $banner->image])" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin::empty-state :title="__('admin.empty.banners')"
                                    :message="__('admin.messages.no_banners')" icon="pages">
                                    <a href="{{ route('admin.banners.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.banners.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$banners" />
    </div>
@endsection
