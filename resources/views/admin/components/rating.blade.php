@props([
    'value' => 0,
    'size' => 'sm',
    'showValue' => true,
])

@php
    $score = (float) $value;
    $filled = (int) round($score);
    $sizeClass = $size === 'lg' ? 'size-5' : 'size-4';
@endphp

<span class="inline-flex items-center gap-1" role="img"
    aria-label="{{ __('admin.cards.stars', ['count' => $filled]) }}">
    @for ($star = 1; $star <= 5; $star++)
        <svg class="{{ $sizeClass }} {{ $star <= $filled ? 'text-warning-500' : 'text-gray-300 dark:text-gray-600' }}"
            width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M10 1.5l2.47 5.006 5.53.805-4 3.896.944 5.5L10 14.157 5.056 16.71l.944-5.5-4-3.896 5.53-.805L10 1.5z" />
        </svg>
    @endfor

    @if ($showValue)
        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ number_format($score, 1) }}</span>
    @endif
</span>
