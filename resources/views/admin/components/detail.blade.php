@props([
    'label' => null,
    'value' => null,
    'colspan' => 1,
])

<div class="{{ $colspan === 2 ? 'sm:col-span-2' : '' }}">
    <dt class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
        {{ $label }}
    </dt>
    <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">
        {{ $slot->isEmpty() ? ($value ?? '—') : $slot }}
    </dd>
</div>
