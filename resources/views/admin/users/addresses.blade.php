@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="__('admin.pages.users.addresses', ['name' => $user->name])">
        <a href="{{ route('admin.users.index') }}"
            class="bg-white text-gray-700 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium ring-1 ring-inset transition">
            {{ __('admin.text.back_to_customers') }}
        </a>
        <a href="{{ route('admin.users.edit', $user) }}"
            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
            {{ __('admin.common.edit') }}
        </a>
    </x-admin::page-header>

    <x-admin::flash />

    <x-admin::card :title="__('admin.form.sections.customer')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::detail :label="__('admin.form.labels.name')" :value="$user->name" />

            <x-admin::detail :label="__('admin.form.labels.phone')"
                :value="$user->phone ?: __('admin.common.not_set')" />

            <x-admin::detail :label="__('admin.form.labels.email')"
                :value="$user->email ?: __('admin.common.not_set')" />

            <x-admin::detail :label="__('admin.form.labels.city')"
                :value="$user->city?->name_ar ?: __('admin.common.not_set')" />
        </div>
    </x-admin::card>

    <div class="table-shell">
        <div class="table-scroll">
            <table class="w-full min-w-max text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="th"><p class="th-label">{{ __('admin.th.title') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.address') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.coordinates') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.default') }}</p></th>
                        <th class="th"><p class="th-label">{{ __('admin.th.added') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addresses as $address)
                        <tr class="table-row">
                            <td class="td">
                                <p class="td-strong">{{ ucfirst((string) $address->title) }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $address->full_address }}</p>
                            </td>
                            <td class="td">
                                <p class="td-text font-mono text-xs">{{ $address->latitude }},
                                    {{ $address->longitude }}</p>
                            </td>
                            <td class="td">
                                @if ($address->is_default)
                                    <x-admin::badge size="sm" color="success">{{ __('admin.common.default') }}</x-admin::badge>
                                @else
                                    <p class="td-text">—</p>
                                @endif
                            </td>
                            <td class="td">
                                <p class="td-text">{{ $address->created_at?->diffForHumans() }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin::empty-state :title="__('admin.empty.addresses')" icon="ecommerce" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin::pagination :paginator="$addresses" />
    </div>
@endsection
