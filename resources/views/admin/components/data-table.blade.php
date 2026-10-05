@props([
    'headers' => [],
    'resource' => null,
    'emptyTitle' => 'No records found',
    'emptyMessage' => '',
    'emptyIcon' => 'tables',
])

@php
    $rows = $resource instanceof \Illuminate\Contracts\Pagination\Paginator || $resource instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $resource->items()
        : ($resource instanceof \Illuminate\Support\Collection ? $resource : collect($resource ?? []));
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    <div class="overflow-x-auto">
        <table class="w-full min-w-max text-start">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th
                            class="border-b border-gray-100 bg-gray-50/50 px-6 py-3.5 align-middle text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400 {{ is_string($header) ? '' : ($header['class'] ?? '') }}">
                            {{ is_string($header) ? $header : $header['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $row)
                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                        {{ $slot }}
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(count($headers), 1) }}">
                            <x-admin::empty-state :title="$emptyTitle" :message="$emptyMessage"
                                :icon="$emptyIcon" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($resource instanceof \Illuminate\Contracts\Pagination\Paginator || $resource instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <x-admin::pagination :paginator="$resource" />
    @endif
</div>
