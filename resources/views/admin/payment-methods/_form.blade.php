@php
    $editing = isset($paymentMethod);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="Payment method details">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="key" label="Key" :value="$paymentMethod->key ?? null" required
                placeholder="apple_pay" hint="Machine key used by the app. Letters, numbers, dashes and underscores only." />

            <x-admin::form.input name="sort_order" label="Sort order" type="number" :min="0" :step="1"
                :value="$paymentMethod->sort_order ?? 0" />

            <x-admin::form.input name="name_ar" label="Name (Arabic)" :value="$paymentMethod->name_ar ?? null"
                required placeholder="الدفع عند الاستلام" />

            <x-admin::form.input name="name_en" label="Name (English)" :value="$paymentMethod->name_en ?? null"
                required placeholder="Cash on delivery" />

            <x-admin::form.image name="icon" label="Icon" colspan="2" :current="$paymentMethod->icon ?? null"
                accept="image/*" hint="SVG, JPG, PNG or WEBP up to 2 MB." />

            <div class="flex items-end sm:col-span-2">
                <x-admin::form.toggle name="is_active" label="Active" :checked="$paymentMethod->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.payment-methods.index')" />
</form>
