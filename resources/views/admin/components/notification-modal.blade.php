@props([
    'action',
    'types' => [],
    'recipient' => null,
])

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="inline-block">
    <button type="button" x-on:click="open = true" class="icon-action"
        aria-label="{{ __('admin.form.labels.send_notification') }}"
        title="{{ __('admin.form.labels.send_notification') }}">
        <svg class="stroke-current" width="18" height="18" viewBox="0 0 24 24" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"
                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity.duration.200ms x-on:click="open = false"
            class="absolute inset-0 bg-gray-900/60" aria-hidden="true"></div>

        <div x-show="open" x-transition.duration.200ms
            class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-lg dark:bg-gray-900"
            role="dialog" aria-modal="true">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ __('admin.form.sections.notification') }}
            </h3>

            @if ($recipient)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $recipient }}</p>
            @endif

            <form method="POST" action="{{ $action }}" class="mt-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-admin::form.input name="title_ar" :label="__('admin.form.labels.title_ar')" required />

                    <x-admin::form.input name="title_en" :label="__('admin.form.labels.title_en')" />

                    <x-admin::form.input name="message_ar" type="textarea" :label="__('admin.form.labels.message_ar')"
                        required />

                    <x-admin::form.input name="message_en" type="textarea" :label="__('admin.form.labels.message_en')" />

                    <x-admin::form.select name="type" :label="__('admin.common.type')" :options="$types"
                        :value="'system'" />

                    <x-admin::form.input name="action_label_ar" :label="__('admin.form.labels.action_label_ar')" />

                    <x-admin::form.input name="action_label_en" :label="__('admin.form.labels.action_label_en')" />
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" x-on:click="open = false"
                        class="shadow-theme-xs hover:bg-gray-50 dark:hover:bg-white/5 dark:hover:text-gray-300 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition dark:border-gray-700 dark:text-gray-400">
                        {{ __('admin.common.cancel') }}
                    </button>

                    <button type="submit"
                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                        {{ __('admin.text.send') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
