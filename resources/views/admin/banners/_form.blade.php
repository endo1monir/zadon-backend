@php
    $editing = isset($banner);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="Banner image">
        <div class="grid grid-cols-1 gap-6">
            <x-admin::form.image name="image" label="Image" :current="$banner->image ?? null" :required="! $editing"
                hint="JPG, PNG or WEBP up to 2 MB. Wide images work best for home page banners." />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.banners.index')" />
</form>
