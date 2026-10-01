@props([
    'src' => null,
    'alt' => '',
    'fallback' => 'U',
    'size' => 'md',
    'class' => '',
])

@php
    $sizeMap = [
        'xs' => 'size-6 text-[10px]',
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-12 text-base',
        'xl' => 'size-14 text-lg',
    ];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => "rounded-full object-cover ring-2 ring-white dark:ring-gray-900 {$sizeClass} {$class}"]) }}
        loading="lazy"
    />
@else
    <span
        {{ $attributes->merge(['class' => "bg-brand-500 text-white flex items-center justify-center rounded-full font-semibold uppercase {$sizeClass} {$class}"]) }}
    >
        {{ $fallback }}
    </span>
@endif
