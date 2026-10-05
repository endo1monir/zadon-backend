@php
    $editing = isset($product);
    $currentProduct = $product ?? null;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.product_details')"
        :desc="__('admin.form.descs.vendor_create_product')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name_ar" :label="__('admin.form.labels.name_ar')"
                :value="$currentProduct->name_ar ?? null" required placeholder="مثال: لبن جهينة" />

            <x-admin::form.input name="name_en" :label="__('admin.form.labels.name_en')"
                :value="$currentProduct->name_en ?? null" placeholder="Juhaina Milk" />

            <x-admin::form.select name="category_id" :label="__('admin.form.labels.category')" :options="$categoryOptions"
                :value="$currentProduct->category_id ?? null" :placeholder="__('admin.form.placeholders.no_category')" />

            <x-admin::form.input name="sku" :label="__('admin.form.labels.sku')" :value="$currentProduct->sku ?? null"
                placeholder="MILK-1L" />

            <x-admin::form.input name="barcode" :label="__('admin.form.labels.barcode')"
                :value="$currentProduct->barcode ?? null" placeholder="6281000112233" />

            <x-admin::form.input name="country_of_origin" :label="__('admin.form.labels.country_of_origin')"
                :value="$currentProduct->country_of_origin ?? null" placeholder="السعودية" />

            <x-admin::form.input name="description_ar" :label="__('admin.form.labels.description_ar')" type="textarea"
                colspan="2" :value="$currentProduct->description_ar ?? null" />

            <x-admin::form.input name="description_en" :label="__('admin.form.labels.description_en')" type="textarea"
                colspan="2" :value="$currentProduct->description_en ?? null" />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_prescription_required" :label="__('admin.form.labels.prescription_required')"
                    :checked="$currentProduct->is_prescription_required ?? false" />
            </div>

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" :label="__('admin.common.active')"
                    :checked="$currentProduct->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.pricing_and_stock')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="price" :label="__('admin.form.labels.price')" type="number" :min="0" :step="0.01"
                required :value="$currentProduct->price ?? null" />

            <x-admin::form.input name="original_price" :label="__('admin.form.labels.original_price')" type="number"
                :min="0" :step="0.01" :value="$currentProduct->original_price ?? null"
                :hint="__('admin.form.hints.original_price_hint')" />

            <x-admin::form.input name="cost_price" :label="__('admin.form.labels.cost_price')" type="number" :min="0"
                :step="0.01" :value="$currentProduct->cost_price ?? 0" />

            <x-admin::form.input name="stock" :label="__('admin.form.labels.stock')" type="number" :min="0" :step="1"
                :value="$currentProduct->stock ?? 0" />

            <x-admin::form.input name="min_stock_alert" :label="__('admin.form.labels.low_stock_alert')" type="number"
                :min="0" :step="1" :value="$currentProduct->min_stock_alert ?? 0"
                :hint="__('admin.form.hints.low_stock_threshold')" />

            <x-admin::form.input name="unit_ar" :label="__('admin.form.labels.unit_ar')"
                :value="$currentProduct->unit_ar ?? null" required placeholder="قطعة" />

            <x-admin::form.input name="unit_en" :label="__('admin.form.labels.unit_en')"
                :value="$currentProduct->unit_en ?? null" placeholder="piece" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.storage')" :desc="__('admin.form.descs.cold_storage_optional')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.select name="storage_temp" :label="__('admin.form.labels.storage_temperature')"
                :options="$storageTempOptions" :value="$currentProduct->storage_temp ?? null"
                :placeholder="__('admin.form.placeholders.not_set')" />

            <x-admin::form.input name="storage_method" :label="__('admin.form.labels.storage_method')"
                :value="$currentProduct->storage_method ?? null" placeholder="冷藏" />

            <x-admin::form.input name="expiry_date" :label="__('admin.form.labels.expiry_date')" type="date"
                :value="$currentProduct?->expiry_date?->format('Y-m-d')" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.image')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.image name="image" :label="__('admin.form.labels.product_image')"
                :current="$currentProduct->image ?? null" accept="image/*"
                :hint="__('admin.form.hints.image_types')" />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.vendors.products.index', $vendor)" />
</form>
