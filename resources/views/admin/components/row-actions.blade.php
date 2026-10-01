@props([
    'editUrl' => null,
    'deleteUrl' => null,
    'deleteMessage' => 'Are you sure you want to delete this item?',
    'showUrl' => null,
])

<div class="flex items-center justify-end gap-1">
    @if ($showUrl)
        <a href="{{ $showUrl }}" class="icon-action" title="View">
            {!! \App\Support\AdminIcons::svg('user-profile') !!}
        </a>
    @endif

    {{ $slot }}

    @if ($editUrl)
        <a href="{{ $editUrl }}" class="icon-action" title="Edit">
            {!! \App\Support\AdminIcons::svg('forms') !!}
        </a>
    @endif

    @if ($deleteUrl)
        <x-admin::delete-modal :action="$deleteUrl" :message="$deleteMessage" />
    @endif
</div>
