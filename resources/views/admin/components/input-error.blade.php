@props(['name', 'label' => null])

@php
    $inputId = $attributes->get('id', $name);
    $messages = $errors->get($name);
@endphp

@if ($messages)
    <div {{ $attributes->only('class')->merge(['class' => 'mt-1.5']) }}>
        @foreach ($messages as $message)
            <p class="text-error-600 dark:text-error-400 text-xs">{{ $message }}</p>
        @endforeach
    </div>
@endif
