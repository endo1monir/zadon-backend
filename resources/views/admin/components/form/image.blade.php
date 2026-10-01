@props([
    'name',
    'label' => null,
    'current' => null,
    'hint' => null,
    'required' => false,
    'accept' => 'image/*',
    'colspan' => 1,
    'rounded' => 'rounded-xl',
])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $src = $current ? api_image($current) : null;
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

    <div x-data="{ preview: @js($src) }" class="flex flex-wrap items-center gap-4">
        <div
            class="border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-white/5 flex size-24 shrink-0 items-center justify-center overflow-hidden border {{ $rounded }} border-dashed">
            <template x-if="preview">
                <img :src="preview" alt="Preview" class="size-full object-cover" />
            </template>
            <template x-if="! preview">
                <span class="text-gray-400 dark:text-gray-500">
                    {!! \App\Support\AdminIcons::svg('pages') !!}
                </span>
            </template>
        </div>

        <div class="min-w-0 flex-1">
            <input type="file" id="{{ $id }}" name="{{ $name }}" accept="{{ $accept }}"
                @if ($required) required @endif
                x-on:change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview"
                {{
                    $attributes->except(['id', 'class'])->merge([
                        'class' => 'text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-white/5 dark:file:text-gray-300',
                    ])
                }} />
            @if ($hint)
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
            @endif
        </div>
    </div>

    <x-admin::input-error :name="$name" />
</div>
