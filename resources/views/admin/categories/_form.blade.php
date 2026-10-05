@php
    $editing = isset($category);
    $categoryType = old('type', $selectedType ?? $category->type ?? 'store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.category_details')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.select name="type" :label="__('admin.form.labels.used_for')" required
                :options="$typeOptions" :value="$categoryType" />

            <x-admin::form.select name="parent_id" :label="__('admin.form.labels.parent_category')"
                :options="$parentOptions" :value="$category->parent_id ?? null"
                :placeholder="__('admin.form.placeholders.no_parent')" />

            <x-admin::form.input name="name_ar" :label="__('admin.form.labels.name_ar')" :value="$category->name_ar ?? null"
                required placeholder="مثال: بقالة" />

            <x-admin::form.input name="name_en" :label="__('admin.form.labels.name_en')" :value="$category->name_en ?? null"
                placeholder="Groceries" />

            <x-admin::form.input name="slug" :label="__('admin.form.labels.slug')" :value="$category->slug ?? null"
                placeholder="auto-generated-from-the-name" :hint="__('admin.form.hints.slug_rules')" />

            <x-admin::form.input name="icon" :label="__('admin.form.labels.icon_identifier')" :value="$category->icon ?? null"
                placeholder="ecommerce" :hint="__('admin.form.hints.icon_identifier_hint')" />

            <x-admin::form.input name="sort_order" :label="__('admin.form.labels.sort_order')" type="number" :min="0"
                :step="1" :value="$category->sort_order ?? 0" :hint="__('admin.form.hints.sort_order_hint')" />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" :label="__('admin.common.active')"
                    :checked="$category->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.categories.index')" />
</form>
