@php
    $editing = isset($product);
    $currentProduct = $product ?? null;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="Product details" desc="The same fields the vendor collects on POST /api/vendor/products.">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name_ar" label="Name (Arabic)" :value="$currentProduct->name_ar ?? null" required
                placeholder="مثال: لبن جهينة" />

            <x-admin::form.input name="name_en" label="Name (English)" :value="$currentProduct->name_en ?? null"
                placeholder="Juhaina Milk" />

            <x-admin::form.select name="category_id" label="Category" :options="$categoryOptions"
                :value="$currentProduct->category_id ?? null" placeholder="No category" />

            <x-admin::form.input name="sku" label="SKU" :value="$currentProduct->sku ?? null" placeholder="MILK-1L" />

            <x-admin::form.input name="barcode" label="Barcode" :value="$currentProduct->barcode ?? null"
                placeholder="6281000112233" />

            <x-admin::form.input name="country_of_origin" label="Country of origin"
                :value="$currentProduct->country_of_origin ?? null" placeholder="السعودية" />

            <x-admin::form.input name="description_ar" label="Description (Arabic)" type="textarea" colspan="2"
                :value="$currentProduct->description_ar ?? null" />

            <x-admin::form.input name="description_en" label="Description (English)" type="textarea" colspan="2"
                :value="$currentProduct->description_en ?? null" />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_prescription_required" label="Prescription required"
                    :checked="$currentProduct->is_prescription_required ?? false" />
            </div>

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" label="Active" :checked="$currentProduct->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::card title="Pricing and stock">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="price" label="Price" type="number" :min="0" :step="0.01" required
                :value="$currentProduct->price ?? null" />

            <x-admin::form.input name="original_price" label="Original price" type="number" :min="0" :step="0.01"
                :value="$currentProduct->original_price ?? null" hint="Shown struck through when it is higher than the price." />

            <x-admin::form.input name="cost_price" label="Cost price" type="number" :min="0" :step="0.01"
                :value="$currentProduct->cost_price ?? 0" />

            <x-admin::form.input name="stock" label="Stock" type="number" :min="0" :step="1"
                :value="$currentProduct->stock ?? 0" />

            <x-admin::form.input name="min_stock_alert" label="Low stock alert" type="number" :min="0" :step="1"
                :value="$currentProduct->min_stock_alert ?? 0"
                hint="The product is flagged as low stock once stock drops to this number." />

            <x-admin::form.input name="unit_ar" label="Unit (Arabic)" :value="$currentProduct->unit_ar ?? null" required
                placeholder="قطعة" />

            <x-admin::form.input name="unit_en" label="Unit (English)" :value="$currentProduct->unit_en ?? null"
                placeholder="piece" />
        </div>
    </x-admin::card>

    <x-admin::card title="Storage" desc="Leave these empty for products that do not need cold storage.">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.select name="storage_temp" label="Storage temperature" :options="$storageTempOptions"
                :value="$currentProduct->storage_temp ?? null" placeholder="Not set" />

            <x-admin::form.input name="storage_method" label="Storage method" :value="$currentProduct->storage_method ?? null"
                placeholder="冷藏" />

            <x-admin::form.input name="expiry_date" label="Expiry date" type="date"
                :value="$currentProduct?->expiry_date?->format('Y-m-d')" />
        </div>
    </x-admin::card>

    <x-admin::card title="Image">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.image name="image" label="Product image" :current="$currentProduct->image ?? null"
                accept="image/*" hint="JPG, PNG or WEBP up to 2 MB." />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action"
        :cancel="route('admin.vendors.products.index', $vendor)" />
</form>
