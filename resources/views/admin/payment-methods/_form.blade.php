@php
    $editing = isset($paymentMethod);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.payment_method_details')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="key" :label="__('admin.form.labels.key')" :value="$paymentMethod->key ?? null" required
                placeholder="apple_pay" :hint="__('admin.form.hints.machine_key')" />

            <x-admin::form.input name="sort_order" :label="__('admin.form.labels.sort_order')" type="number" :min="0"
                :step="1" :value="$paymentMethod->sort_order ?? 0" :hint="__('admin.form.hints.sort_order_hint')" />

            <x-admin::form.input name="name_ar" :label="__('admin.form.labels.name_ar')" :value="$paymentMethod->name_ar ?? null"
                required placeholder="الدفع عند الاستلام" />

            <x-admin::form.input name="name_en" :label="__('admin.form.labels.name_en')" :value="$paymentMethod->name_en ?? null"
                required placeholder="Cash on delivery" />

            <x-admin::form.image name="icon" :label="__('admin.form.labels.icon')" colspan="2"
                :current="$paymentMethod->icon ?? null" accept="image/*" :hint="__('admin.form.hints.icon_types')" />

            <div class="flex items-end sm:col-span-2">
                <x-admin::form.toggle name="is_active" :label="__('admin.common.active')"
                    :checked="$paymentMethod->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.payment-methods.index')" />
</form>
