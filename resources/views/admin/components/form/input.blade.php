@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'rows' => 3,
    'step' => null,
    'min' => null,
    'max' => null,
    'colspan' => 1,
])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $current = old($name, $value);
@endphp

<div class="{{ $colspan === 2 ? 'sm:col-span-2' : '' }}">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
            @if ($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            {{
                $attributes->except(['id', 'class'])->merge([
                    'class' => 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                ])
            }}>{{ $current }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            value="{{ $type === 'password' ? '' : $current }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($step !== null) step="{{ $step }}" @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null) max="{{ $max }}" @endif
            {{
                $attributes->except(['id', 'class'])->merge([
                    'class' => 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                ])
            }} />
    @endif

    @if ($hint)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    <x-admin::input-error :name="$name" />
</div>
