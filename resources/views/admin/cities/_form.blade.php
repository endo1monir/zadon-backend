@php
    $editing = isset($city);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="City details">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name_ar" label="Name (Arabic)" :value="$city->name_ar ?? null" required
                placeholder="مثال: القاهرة" />

            <x-admin::form.input name="name_en" label="Name (English)" :value="$city->name_en ?? null"
                placeholder="Cairo" />

            <x-admin::form.input name="sort_order" label="Sort order" type="number" :min="0" :step="1"
                :value="$city->sort_order ?? 0"
                hint="Lower values appear first in public selects." />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" label="Active" :checked="$city->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.cities.index')" />
</form>
