@props([
    'title' => 'No records found',
    'message' => '',
    'icon' => 'tables',
])

<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <div class="bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500 flex size-12 items-center justify-center rounded-full">
        {!! \App\Support\AdminIcons::svg($icon) !!}
    </div>

    <h3 class="mt-4 text-sm font-semibold text-gray-800 dark:text-white/90">
        {{ $title }}
    </h3>

    @if ($message)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $message }}
        </p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
