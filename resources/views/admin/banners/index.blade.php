@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Banners" />

    <x-admin::flash />

    <x-admin::table-toolbar create-route="admin.banners.create" create-label="Upload banner" />

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">Preview</p></th>
                        <th class="th"><p class="th-label">File</p></th>
                        <th class="th"><p class="th-label">Uploaded</p></th>
                        <th class="th text-end"><p class="th-label">Actions</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr class="table-row">
                            <td class="td">
                                <img src="{{ api_image($banner->image) }}" alt="Banner {{ $banner->id }}"
                                    class="h-14 w-32 rounded-lg object-cover" />
                            </td>
                            <td class="td"><p class="td-text font-mono text-xs">{{ $banner->image }}</p></td>
                            <td class="td"><p class="td-text">{{ $banner->created_at?->diffForHumans() }}</p></td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.banners.edit', $banner)"
                                    :delete-url="route('admin.banners.destroy', $banner)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin::empty-state title="No banners yet"
                                    message="Upload a banner image to show it on the home page." icon="pages">
                                    <a href="{{ route('admin.banners.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">Upload banner</a>
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
