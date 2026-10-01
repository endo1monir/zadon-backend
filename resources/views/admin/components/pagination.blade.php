@props(['paginator'])

@if ($paginator->hasPages())
    <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-4 py-4 sm:flex-row sm:px-6 dark:border-gray-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->total() }}</span>
        </p>

        {{ $paginator->onEachSide(1)->links() }}
    </div>
@endif
