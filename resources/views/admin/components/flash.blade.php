@if (session('success') || session('status'))
    <x-admin::alert variant="success" :title="session('success') ?? session('status')" class="mb-6" />
@endif

@if (session('error'))
    <x-admin::alert variant="error" :title="session('error')" class="mb-6" />
@endif

@if ($errors->any() && ! isset($suppressValidationErrors))
    <x-admin::alert variant="error" :title="__('admin.form.sections.errors')" class="mb-6">
        <ul class="mt-2 list-disc space-y-1 ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin::alert>
@endif
