@php
    $editing = isset($admin);
    $passwordHint = $editing
        ? __('admin.form.hints.keep_current_password')
        : __('admin.form.hints.password_min_length');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.admin_details')" :desc="__('admin.form.hints.dashboard_login')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name" :label="__('admin.form.labels.name')" :value="$admin->name ?? null"
                required />

            <x-admin::form.input name="email" type="email" :label="__('admin.form.labels.email')"
                :value="$admin->email ?? null" required />

            <x-admin::form.input name="phone" :label="__('admin.form.labels.phone')" :value="$admin->phone ?? null" />

            <x-admin::form.select name="city_id" :label="__('admin.form.labels.city')" :options="$cityOptions ?? []"
                :value="$admin->city_id ?? null" :placeholder="__('admin.form.placeholders.select_city')" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.access')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="password" type="password" :label="__('admin.form.labels.password')"
                :hint="$passwordHint" :required="! $editing" />

            <x-admin::form.image name="avatar" :label="__('admin.form.labels.avatar')" :current="$admin->avatar ?? null"
                accept="image/*" :hint="__('admin.form.hints.image_types')" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.status')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" :label="__('admin.form.labels.account_active')"
                    :checked="$admin->is_active ?? true" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.admins.index')" />
</form>
