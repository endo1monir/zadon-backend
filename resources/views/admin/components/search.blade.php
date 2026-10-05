@props(['action', 'placeholder' => null, 'name' => 'search'])

@php
    $placeholder = $placeholder ?? __('admin.common.search');
@endphp

<div class="w-full sm:max-w-xs">
    <form method="GET" action="{{ $action }}" class="relative">
        @foreach ($filters ?? [] as $key => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
            @endif
        @endforeach

        <input type="search" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pe-10 ps-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />

        <button type="submit"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            aria-label="{{ __('admin.common.search') }}">
            <svg class="stroke-current" width="18" height="18" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M21 21L18.65 18.65M18 11C18 15.4183 14.4183 19 10 19C5.58172 19 2 15.4183 2 11C2 6.58172 5.58172 3 10 3C14.4183 3 18 6.58172 18 11Z"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </form>
</div>
