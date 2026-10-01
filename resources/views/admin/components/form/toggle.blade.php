@props([
    'name',
    'label' => null,
    'checked' => false,
    'hint' => null,
    'colspan' => 1,
])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="{{ $colspan === 2 ? 'sm:col-span-2' : '' }}">
    <label for="{{ $id }}" class="flex cursor-pointer items-center gap-3">
        <input type="hidden" name="{{ $name }}" value="0" />
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($isChecked)
            class="peer sr-only" />
        <span
            class="bg-gray-200 dark:bg-white/10 relative h-6 w-11 rounded-full transition peer-checked:bg-brand-500 peer-focus:ring-3 peer-focus:ring-brand-500/20 after:absolute after:start-[2px] after:top-[2px] after:size-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full rtl:after:start-0 rtl:after:translate-x-full rtl:peer-checked:after:-translate-x-full">
        </span>
        @if ($label)
            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ $label }}</span>
        @endif
    </label>

    @if ($hint)
        <p class="mt-1.5 ps-14 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    <x-admin::input-error :name="$name" />
</div>
