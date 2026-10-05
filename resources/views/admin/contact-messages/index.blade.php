@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.contact_messages.title')" />

    <x-admin::flash />

    <x-admin::table-toolbar :search-action="route('admin.contact-messages.index')"
        :search-placeholder="__('admin.search.contact_messages')" :filters="$filters">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="w-full sm:w-44">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="from_user" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">{{ __('admin.filters.all_senders') }}</option>
                <option value="1" @selected(request('from_user') === '1')>{{ __('admin.filters.registered_users') }}</option>
                <option value="0" @selected(request('from_user') === '0')>{{ __('admin.filters.guests') }}</option>
            </select>
        </form>
    </x-admin::table-toolbar>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.title') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.message') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.sender') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.received') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="table-row">
                            <td class="td">
                                <a href="{{ route('admin.contact-messages.show', $message) }}" class="td-strong hover:text-brand-500">
                                    {{ $message->title }}
                                </a>
                            </td>
                            <td class="td">
                                <p class="td-text max-w-md whitespace-normal">
                                    {{ \Illuminate\Support\Str::limit($message->message, 90) }}</p>
                            </td>
                            <td class="td">
                                @if ($message->user)
                                    <p class="td-strong">{{ $message->user->name ?: '—' }}</p>
                                    <p class="td-text">{{ $message->user->phone }}</p>
                                @else
                                    <x-admin::badge size="sm" color="light">{{ __('admin.th.guest') }}</x-admin::badge>
                                @endif
                            </td>
                            <td class="td"><p class="td-text">{{ $message->created_at?->diffForHumans() }}</p></td>
                            <td class="td">
                                <x-admin::row-actions :show-url="route('admin.contact-messages.show', $message)"
                                    :delete-url="route('admin.contact-messages.destroy', $message)"
                                    :delete-message="__('admin.delete.contact_message', ['name' => $message->title])" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin::empty-state :title="__('admin.empty.contact_messages')"
                                    :message="__('admin.messages.no_contact_messages')" icon="chat" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$messages" />
    </div>
@endsection
