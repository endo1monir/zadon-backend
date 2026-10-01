@extends('admin.layouts.app')

@section('content')
    <x-admin::page-header :title="'Edit category: '.$category->name_ar">
        <a href="{{ route('admin.categories.index') }}"
            class="bg-white text-gray-700 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/5 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium ring-1 ring-inset transition">
            Back to list
        </a>
    </x-admin::page-header>

    <x-admin::flash />

    @include('admin.categories._form', [
        'action' => route('admin.categories.update', $category),
        'selectedType' => $category->type,
        'typeOptions' => \App\Support\AdminOptions::categoryTypes(),
        'parentOptions' => \App\Support\AdminOptions::categories($category->type, ignoreId: $category->id),
    ])
@endsection
