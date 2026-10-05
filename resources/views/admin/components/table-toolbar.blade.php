@props([
    'title' => null,
    'createRoute' => null,
    'createUrl' => null,
    'createLabel' => null,
    'searchPlaceholder' => null,
    'searchAction' => null,
    'filters' => [],
])

@php
    $createLabel = $createLabel ?? __('admin.common.add_new');
    $createUrl = $createUrl ?? ($createRoute ? route($createRoute) : null);
@endphp

<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex flex-wrap items-center gap-3">
        @if ($searchAction)
            <x-admin::search :action="$searchAction" :placeholder="$searchPlaceholder" :filters="$filters" />
        @endif
        {{ $slot }}
    </div>

    @if ($createUrl)
        <a href="{{ $createUrl }}"
            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex shrink-0 items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white transition">
            <svg class="stroke-current" width="18" height="18" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            {{ $createLabel }}
        </a>
    @endif
</div>
