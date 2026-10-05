@props([
    'status',
    'label' => null,
    'success' => 'success',
    'warning' => 'warning',
    'error' => 'error',
    'info' => 'info',
    'neutral' => 'light',
])

@php
    $colorMap = [
        $success => 'success',
        $warning => 'warning',
        $error => 'error',
        $info => 'info',
        $neutral => 'light',
    ];

    $variantMap = [
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400',
        'error' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        'info' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400',
        'light' => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
    ];

    $color = $colorMap[$status] ?? 'light';
    $variantClass = $variantMap[$color] ?? $variantMap['light'];
@endphp

<span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $variantClass }}">
    {{ $slot ?: ($label ?? str($status)->headline()) }}
</span>
