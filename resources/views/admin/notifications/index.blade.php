@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.notifications.title')" />

    <x-admin::flash />

    <form method="POST" action="{{ route('admin.notifications.store') }}" class="mb-6">
        @csrf

        <x-admin::card :title="__('admin.form.sections.notification')" :desc="__('admin.form.descs.broadcast')">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-admin::form.input name="title_ar" :label="__('admin.form.labels.title_ar')" required />

                <x-admin::form.input name="title_en" :label="__('admin.form.labels.title_en')" />

                <x-admin::form.input name="message_ar" type="textarea" :label="__('admin.form.labels.message_ar')"
                    required />

                <x-admin::form.input name="message_en" type="textarea" :label="__('admin.form.labels.message_en')" />

                <x-admin::form.select name="type" :label="__('admin.common.type')" :options="$typeOptions"
                    :value="'system'" />

                <x-admin::form.select name="audience" :label="__('admin.form.labels.audience')" :options="[
                    'clients' => __('admin.options.user_roles.customer'),
                    'vendors' => __('admin.options.user_roles.vendor'),
                    'all' => __('admin.filters.everyone'),
                ]" required />

                <x-admin::form.input name="action_label_ar" :label="__('admin.form.labels.action_label_ar')" />

                <x-admin::form.input name="action_label_en" :label="__('admin.form.labels.action_label_en')" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white transition">
                    {{ __('admin.text.send') }}
                </button>
            </div>
        </x-admin::card>
    </form>

    <x-admin::table-toolbar :search-action="route('admin.notifications.index')"
        :search-placeholder="__('admin.search.notifications')" :filters="$filters">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="w-full sm:w-44">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="type" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">{{ __('admin.filters.all_types') }}</option>
                @foreach ($typeOptions as $typeValue => $typeLabel)
                    <option value="{{ $typeValue }}" @selected(request('type') === $typeValue)>
                        {{ $typeLabel }}
                    </option>
                @endforeach
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
                        <th class="th"><p class="th-label">{{ __('admin.th.type') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.recipient') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.sent') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.read') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr class="table-row">
                            <td class="td">
                                <p class="td-strong">{{ $notification->title_ar }}</p>
                                @if ($notification->title_en)
                                    <p class="td-text">{{ $notification->title_en }}</p>
                                @endif
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $notification->message_ar ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" color="light">
                                    {{ $typeOptions[$notification->type] ?? $notification->type }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <x-admin::avatar :src="$notification->user?->avatar ? api_image($notification->user->avatar) : null"
                                        :alt="$notification->user?->name ?? ''"
                                        :fallback="mb_substr($notification->user?->name ?? '?', 0, 1)" size="sm" />
                                    <p class="td-text">{{ $notification->user?->name ?: '—' }}</p>
                                </div>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $notification->created_at?->diffForHumans() }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$notification->is_read ? 'success' : 'light'">
                                    {{ $notification->is_read ? __('admin.common.yes') : __('admin.common.no') }}
                                </x-admin::badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin::empty-state :title="__('admin.empty.notifications')"
                                    :message="__('admin.messages.no_notifications')" icon="email" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$notifications" />
    </div>
@endsection
