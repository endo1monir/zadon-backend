@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.socials.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.socials.index')" :search-placeholder="__('admin.search.socials')"
        :filters="$filters" create-route="admin.socials.create" :create-label="__('admin.pages.socials.create')" />

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.name') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.link') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.preview') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($socials as $social)
                        <tr class="table-row">
                            <td class="td">
                                <p class="td-strong">{{ $social->name_ar }}</p>
                                @if ($social->name_en)
                                    <p class="td-text">{{ $social->name_en }}</p>
                                @endif
                            </td>
                            <td class="td">
                                <a href="{{ $social->link }}" target="_blank" rel="noopener noreferrer"
                                    class="text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 break-all text-sm font-medium">
                                    {{ $social->link }}
                                </a>
                            </td>
                            <td class="td">
                                @if ($social->icon)
                                    <img src="{{ api_image($social->icon) }}" alt="{{ $social->name_ar }}"
                                        class="size-10 rounded-lg object-contain" />
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="td">
                                <x-admin::row-actions :edit-url="route('admin.socials.edit', $social)"
                                    :delete-url="route('admin.socials.destroy', $social)"
                                    :delete-message="__('admin.delete.social', ['name' => $social->name_ar])" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin::empty-state :title="__('admin.empty.socials')"
                                    :message="__('admin.messages.no_socials')" icon="chat">
                                    <a href="{{ route('admin.socials.create') }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">{{ __('admin.pages.socials.create') }}</a>
                                </x-admin::empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$socials" />
    </div>
@endsection
