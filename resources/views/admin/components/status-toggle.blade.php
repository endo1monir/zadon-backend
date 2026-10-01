@props([
    'action',
    'active' => true,
    'label' => null,
])

<form method="POST" action="{{ $action }}" class="inline-block">
    @csrf
    @method('PATCH')
    <button type="submit" class="icon-action"
        title="{{ $label ?? ($active ? 'Set inactive' : 'Set active') }}">
        {!! \App\Support\AdminIcons::svg($active ? 'authentication' : 'calendar') !!}
    </button>
</form>
