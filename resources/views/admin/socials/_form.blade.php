<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if (isset($social))
        @method('PUT')
    @endif

    <x-admin::card :title="__('admin.form.sections.social_details')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::form.input name="name_ar" :label="__('admin.form.labels.name_ar')" :value="$social->name_ar ?? null"
                required />

            <x-admin::form.input name="name_en" :label="__('admin.form.labels.name_en')" :value="$social->name_en ?? null" />

            <x-admin::form.input name="link" type="url" :label="__('admin.form.labels.link')" :value="$social->link ?? null"
                :hint="__('admin.form.hints.full_url')" required />

            <x-admin::form.image name="icon" :label="__('admin.form.labels.icon')" :current="$social->icon ?? null"
                accept="image/*" :hint="__('admin.form.hints.icon_types_footer')" />
        </div>
    </x-admin::card>

    <x-admin::form-actions :action="$action" :cancel="route('admin.socials.index')" />
</form>
