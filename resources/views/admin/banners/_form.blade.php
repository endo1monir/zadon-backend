@php
    $editing = isset($banner);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.banner_image')">
        <div class="grid grid-cols-1 gap-6">
            <x-admin::form.image name="image" :label="__('admin.form.labels.image')" :current="$banner->image ?? null"
                :required="! $editing" :hint="__('admin.common.image_hint_banner')" />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.banners.index')" />
</form>
