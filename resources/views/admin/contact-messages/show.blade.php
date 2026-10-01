@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header title="Message">
        <a href="{{ route('admin.contact-messages.index') }}"
            class="bg-white text-gray-700 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium ring-1 ring-inset transition">
            Back to list
        </a>
    </x-admin::page-header>

    <x-admin::flash />

    <x-admin::card :title="$message->title" :desc="'Received '.$message->created_at?->diffForHumans()">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-admin::detail label="Sender">
                @if ($message->user)
                    {{ $message->user->name ?: '—' }}
                @else
                    Guest
                @endif
            </x-admin::detail>

            <x-admin::detail label="Phone">{{ $message->user?->phone ?: '—' }}</x-admin::detail>

            <x-admin::detail label="Email">{{ $message->user?->email ?: '—' }}</x-admin::detail>

            <x-admin::detail label="Received at">{{ $message->created_at?->toDayDateTimeString() }}</x-admin::detail>

            <x-admin::detail label="Message" colspan="2">
                <p class="leading-relaxed whitespace-pre-line">{{ $message->message }}</p>
            </x-admin::detail>
        </div>
    </x-admin::card>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <x-admin::delete-modal :action="route('admin.contact-messages.destroy', $message)"
            message="Delete this message? This cannot be undone." variant="button" />
    </div>
@endsection
