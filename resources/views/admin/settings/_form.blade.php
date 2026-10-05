@php
    $editing = isset($setting);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.setting')">
        <div class="grid grid-cols-1 gap-6">
            <x-admin::form.input name="key" :label="__('admin.form.labels.key')" :value="$setting->key ?? null" required
                placeholder="policy_ar" :hint="__('admin.form.hints.machine_key')" />

            <x-admin::form.input name="value" type="textarea" :rows="16" :label="__('admin.form.labels.value')"
                :value="$setting->value ?? null" required />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.settings.index')" />
</form>
