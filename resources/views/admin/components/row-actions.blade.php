@props([
    'editUrl' => null,
    'deleteUrl' => null,
    'deleteMessage' => null,
    'showUrl' => null,
])

@php
    $deleteMessage = $deleteMessage ?? __('admin.common.are_you_sure');
@endphp

<div class="flex items-center justify-end gap-1">
    @if ($showUrl)
        <a href="{{ $showUrl }}" class="icon-action" title="{{ __('admin.common.view') }}">
            {!! \App\Support\AdminIcons::svg('user-profile') !!}
        </a>
    @endif

    {{ $slot }}

    @if ($editUrl)
        <a href="{{ $editUrl }}" class="icon-action" title="{{ __('admin.common.edit') }}">
            {!! \App\Support\AdminIcons::svg('forms') !!}
        </a>
    @endif

    @if ($deleteUrl)
        <x-admin::delete-modal :action="$deleteUrl" :message="$deleteMessage" />
    @endif
</div>
