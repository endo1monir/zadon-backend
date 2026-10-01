@php
    $editing = isset($category);
    $categoryType = old('type', $selectedType ?? $category->type ?? 'store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="Category details">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.select name="type" label="Used for" required
                :options="$typeOptions" :value="$categoryType" />

            <x-admin::form.select name="parent_id" label="Parent category"
                :options="$parentOptions" :value="$category->parent_id ?? null"
                placeholder="No parent (top level)" />

            <x-admin::form.input name="name_ar" label="Name (Arabic)" :value="$category->name_ar ?? null" required
                placeholder="مثال: بقالة" />

            <x-admin::form.input name="name_en" label="Name (English)" :value="$category->name_en ?? null"
                placeholder="Groceries" />

            <x-admin::form.input name="slug" label="Slug" :value="$category->slug ?? null"
                placeholder="auto-generated-from-the-name"
                hint="Leave empty to generate a unique slug automatically. Use letters, numbers, dashes and underscores." />

            <x-admin::form.input name="icon" label="Icon identifier" :value="$category->icon ?? null"
                placeholder="ecommerce" hint="Optional key used by the public site." />

            <x-admin::form.input name="sort_order" label="Sort order" type="number" :min="0" :step="1"
                :value="$category->sort_order ?? 0" />

            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" label="Active" :checked="$category->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.categories.index')" />
</form>
