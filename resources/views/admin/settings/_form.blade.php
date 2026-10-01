@php
    $editing = isset($setting);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-admin::card title="Setting">
        <div class="grid grid-cols-1 gap-6">
            <x-admin::form.input name="key" label="Key" :value="$setting->key ?? null" required
                placeholder="policy_ar" hint="Machine key used by the app. Letters, numbers, dashes and underscores only." />

            <x-admin::form.input name="value" type="textarea" :rows="16" label="Value"
                :value="$setting->value ?? null" required />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.settings.index')" />
</form>
