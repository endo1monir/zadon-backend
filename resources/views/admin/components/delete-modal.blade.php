@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Delete Confirmation',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.',
    'submitLabel' => 'Delete',
    'confirmLabel' => 'Delete',
    'variant' => 'icon',
    'icon' => null,
])

@php
    $variantClasses = [
        'icon' => 'text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 size-9 justify-center rounded-lg',
        'button' => 'text-error-600 border border-error-200 hover:bg-error-50 dark:text-error-400 dark:border-error-500/30 dark:hover:bg-error-500/10 rounded-lg px-3 py-2 text-sm font-medium',
    ];
    $variantClass = $variantClasses[$variant] ?? $variantClasses['icon'];

    $trashIcon = '<svg class="stroke-current" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9V14M12 17.5H12.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    $label = $icon ?? $trashIcon;
@endphp

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="inline-block">
    <button type="button" x-on:click="open = true" aria-label="{{ $confirmLabel }}"
        {{ $attributes->merge(['class' => "inline-flex items-center transition {$variantClass}"]) }}>
        {!! $label !!}
        @if ($variant === 'button')
            <span class="ms-1.5">{{ $confirmLabel }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity.duration.200ms x-on:click="open = false"
            class="absolute inset-0 bg-gray-900/60" aria-hidden="true"></div>

        <div x-show="open" x-transition.duration.200ms
            class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-lg dark:bg-gray-900" role="dialog"
            aria-modal="true">
            <div class="flex flex-col items-center text-center">
                <div class="bg-error-50 text-error-500 dark:bg-error-500/15 mb-4 flex size-14 items-center justify-center rounded-full">
                    {!! $trashIcon !!}
                </div>

                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
            </div>

            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" x-on:click="open = false"
                    class="shadow-theme-xs hover:bg-gray-50 dark:hover:bg-white/5 dark:hover:text-gray-300 flex-1 rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:text-gray-400">
                    Cancel
                </button>

                <form method="POST" action="{{ $action }}" class="flex-1">
                    @csrf
                    @if (strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    <button type="submit"
                        class="bg-error-500 shadow-theme-xs hover:bg-error-600 flex-1 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                        {{ $submitLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
