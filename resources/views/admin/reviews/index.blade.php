@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.reviews.title')" />

    <x-admin::flash />

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin::card :title="__('admin.cards.total_reviews')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['total'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.cards.published')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['published'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.cards.waiting_for_reply')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $summary['unreplied'] }}</p>
        </x-admin::card>

        <x-admin::card :title="__('admin.cards.average_rating')">
            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ number_format((float) $summary['average'], 1) }}
            </p>
        </x-admin::card>
    </div>

    <x-admin::table-toolbar :search-action="route('admin.reviews.index')"
        :search-placeholder="__('admin.search.reviews')" :filters="$filters">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap gap-2">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <select name="store_id" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-44">
                <option value="">{{ __('admin.filters.all_stores') }}</option>
                @foreach ($storeOptions as $storeId => $storeName)
                    <option value="{{ $storeId }}" @selected((string) request('store_id') === (string) $storeId)>
                        {{ $storeName }}
                    </option>
                @endforeach
            </select>

            <select name="rating" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-36">
                <option value="">{{ __('admin.filters.all_ratings') }}</option>
                @foreach ($ratingOptions as $ratingValue => $ratingLabel)
                    <option value="{{ $ratingValue }}" @selected((string) request('rating') === (string) $ratingValue)>
                        {{ $ratingLabel }}
                    </option>
                @endforeach
            </select>

            <select name="published" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-36">
                <option value="">{{ __('admin.filters.all_statuses') }}</option>
                <option value="1" @selected(request('published') === '1')>{{ __('admin.filters.published') }}</option>
                <option value="0" @selected(request('published') === '0')>{{ __('admin.filters.hidden') }}</option>
            </select>

            <select name="replied" onchange="this.form.submit()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-8 ps-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-36">
                <option value="">{{ __('admin.filters.any_reply') }}</option>
                <option value="1" @selected(request('replied') === '1')>{{ __('admin.filters.replied') }}</option>
                <option value="0" @selected(request('replied') === '0')>{{ __('admin.filters.not_replied') }}</option>
            </select>
        </form>
    </x-admin::table-toolbar>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-start">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.customer') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.store') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.rating') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.comment') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.store_reply') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.visibility') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.reviewed') }}</p></th>
                        <th class="th text-end"><p class="th-label">{{ __('admin.th.actions') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr class="table-row">
                            <td class="td">
                                <p class="td-strong">{{ $review->customer_name }}</p>
                                <p class="td-text">{{ $review->customer_phone ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $review->store?->name_ar ?? '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::rating :value="$review->rating" />
                            </td>
                            <td class="td">
                                <p class="td-text max-w-xs">{{ $review->comment ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text max-w-xs">{{ $review->store_reply ?: '—' }}</p>
                            </td>
                            <td class="td">
                                <x-admin::badge size="sm" :color="$review->published ? 'success' : 'light'">
                                    {{ $review->published ? __('admin.filters.published') : __('admin.filters.hidden') }}
                                </x-admin::badge>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $review->created_at?->diffForHumans() }}</p>
                            </td>
                            <td class="td">
                                <x-admin::row-actions
                                    :delete-url="route('admin.reviews.destroy', $review)"
                                    :delete-message="__('admin.delete.review', ['name' => $review->customer_name])">
                                    <x-admin::status-toggle :action="route('admin.reviews.toggle', $review)"
                                        :active="$review->published"
                                        :label="$review->published ? __('admin.filters.hidden') : __('admin.filters.published')" />
                                </x-admin::row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin::empty-state :title="__('admin.empty.reviews')"
                                    :message="__('admin.messages.no_reviews')" icon="support-ticket" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$reviews" />
    </div>
@endsection
