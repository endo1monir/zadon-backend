@php
    $editing = isset($user);
    $passwordHint = $editing
        ? __('admin.form.hints.keep_current_password')
        : __('admin.form.hints.optional_customer_password');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.customer')" :desc="__('admin.form.descs.customer_sign_in')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name" :label="__('admin.form.labels.name')" :value="$user->name ?? null"
                required />

            <x-admin::form.input name="phone" :label="__('admin.form.labels.phone')" :value="$user->phone ?? null"
                required :hint="__('admin.form.hints.otp_login')" />

            <x-admin::form.input name="email" type="email" :label="__('admin.form.labels.email')"
                :value="$user->email ?? null" required />

            <x-admin::form.select name="city_id" :label="__('admin.form.labels.city')" :options="$cityOptions ?? []"
                :value="$user->city_id ?? null" :placeholder="__('admin.form.placeholders.select_city')" />

            <x-admin::form.input name="password" type="password" :label="__('admin.form.labels.password')"
                :hint="$passwordHint" />

            <x-admin::form.image name="avatar" :label="__('admin.form.labels.avatar')" :current="$user->avatar ?? null"
                accept="image/*" :hint="__('admin.form.hints.image_types')" />
        </div>
    </x-admin::card>

    <x-admin::card :title="__('admin.form.sections.status')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="flex items-end">
                <x-admin::form.toggle name="is_active" :label="__('admin.form.labels.account_active')"
                    :checked="$user->is_active ?? true" />
            </div>

            <div class="flex items-end">
                <x-admin::form.toggle name="is_completed" :label="__('admin.form.labels.profile_completed')"
                    :checked="$user->is_completed ?? false" />
            </div>
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.users.index')" />
</form>
