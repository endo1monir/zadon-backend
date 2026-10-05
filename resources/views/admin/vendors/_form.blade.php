@php
    $editing = isset($vendor);
    $currentStore = $store ?? null;
    $passwordHint = $editing
        ? __('admin.form.hints.keep_current_password')
        : __('admin.form.hints.vendor_login');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.vendor_account')" :desc="__('admin.form.descs.vendor_login_details')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name" :label="__('admin.form.labels.owner_name')" :value="$vendor->name ?? null"
                required />

            <x-admin::form.input name="phone" :label="__('admin.form.labels.phone')" :value="$vendor->phone ?? null"
                required />

            <x-admin::form.input name="email" type="email" :label="__('admin.form.labels.email')" :value="$vendor->email ?? null" />

            <x-admin::form.input name="password" type="password" :label="__('admin.form.labels.password')"
                :hint="$passwordHint" :required="! $editing" />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" :label="__('admin.form.labels.account_active')"
                    :checked="$vendor->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.store_and_payment')" :desc="__('admin.form.descs.vendor_register_store')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="store_name_ar" :label="__('admin.form.labels.store_name_ar')"
                :value="$currentStore->name_ar ?? null" required />

            <x-admin::form.input name="store_name_en" :label="__('admin.form.labels.store_name_en')"
                :value="$currentStore->name_en ?? null" />

            <x-admin::form.select name="store_category_id" :label="__('admin.form.labels.category')"
                :options="$categoryOptions ?? []" :value="$currentStore->category_id ?? null"
                :placeholder="__('admin.form.placeholders.no_category')" />

            <x-admin::form.select name="store_city_id" :label="__('admin.form.labels.city')" :options="$cityOptions ?? []"
                :value="$currentStore->city_id ?? null" :placeholder="__('admin.form.placeholders.select_city')" required />

            <x-admin::form.input name="store_address" :label="__('admin.form.labels.address')"
                :value="$currentStore->address ?? null" required />

            <x-admin::form.input name="store_phone" :label="__('admin.form.labels.store_phone')"
                :value="$currentStore->phone ?? null" />

            <x-admin::form.input name="store_email" type="email" :label="__('admin.form.labels.store_email')"
                :value="$currentStore->email ?? null" />

            <x-admin::form.input name="store_manager_name" :label="__('admin.form.labels.manager_name')"
                :value="$currentStore->manager_name ?? null" />

            <x-admin::form.input name="store_cr_number" :label="__('admin.form.labels.commercial_registration')"
                :value="$currentStore->cr_number ?? null" />

            <x-admin::form.input name="store_vat_number" :label="__('admin.form.labels.vat_number')"
                :value="$currentStore->vat_number ?? null" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.operations')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="store_prep_time_min" type="number" :min="1" :max="240" :step="1"
                :label="__('admin.form.labels.prep_time_minutes')" :value="$currentStore->prep_time_min ?? null" />

            <x-admin::form.input name="store_delivery_fee" type="number" :min="0" :step="0.01"
                :label="__('admin.form.labels.delivery_fee')" :value="$currentStore->delivery_fee ?? null" />

            <x-admin::form.input name="store_min_order" type="number" :min="0" :step="0.01"
                :label="__('admin.form.labels.minimum_order')" :value="$currentStore->min_order ?? null" />

            <x-admin::form.input name="store_delivery_radius_km" type="number" :min="1" :max="200" :step="1"
                :label="__('admin.form.labels.delivery_radius')" :value="$currentStore->delivery_radius_km ?? null" />

            <x-admin::form.input name="store_opening_time" type="time"
                :label="__('admin.form.labels.opening_time')" :value="$currentStore->opening_time ?? null" />

            <x-admin::form.input name="store_closing_time" type="time"
                :label="__('admin.form.labels.closing_time')" :value="$currentStore->closing_time ?? null" />

            <x-admin::form.select name="store_status" :label="__('admin.form.labels.store_status')"
                :options="$statusOptions ?? []" :value="$currentStore->status ?? null" />

            <div class="flex items-end gap-6">
                <x-admin::form.toggle name="store_is_open_24_7" :label="__('admin.form.labels.open_24_7')"
                    :checked="$currentStore->is_open_24_7 ?? false" />
            </div>

            <div class="flex items-end">
                <x-admin::form.toggle name="store_is_verified" :label="__('admin.form.labels.verified')"
                    :checked="$currentStore->is_verified ?? false" />
            </div>

            <div class="flex items-end">
                <x-admin::form.toggle name="store_is_active" :label="__('admin.form.labels.store_active')"
                    :checked="$currentStore->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.image')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.image name="store_logo" :label="__('admin.form.labels.logo')" :current="$currentStore->logo ?? null"
                accept="image/*" :hint="__('admin.form.hints.image_types')" />

            <x-admin::form.image name="store_cover_image" :label="__('admin.form.labels.cover_image')"
                :current="$currentStore->cover_image ?? null" accept="image/*" :hint="__('admin.form.hints.image_types')" />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.vendors.index')" />
</form>
