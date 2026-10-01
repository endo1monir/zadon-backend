@props([
    'action',
    'method' => 'POST',
    'label' => 'Save',
    'confirm' => null,
    'variant' => 'primary',
    'cancel' => null,
])

<div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6 dark:border-gray-800"
    x-data="{}">
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs transition
        @if ($variant === 'danger') bg-error-500 hover:bg-error-600
        @elseif ($variant === 'outline') bg-white text-gray-700 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5
        @else bg-brand-500 hover:bg-brand-600 @endif"
        @if ($confirm) x-on:click="if (! window.confirm(@js($confirm))) $event.preventDefault()" @endif>
        {{ $label }}
    </button>

    @if ($cancel)
        <a href="{{ $cancel }}"
            class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 ring-inset transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5">
            Cancel
        </a>
    @endif
</div>
