@props([
    'as' => 'div',
    'padding' => true,
    'hover' => false,
])

@php
    $classes = [
        'ctl-card',
        $padding ? 'p-6' : '',
        $hover ? 'transition-shadow duration-200' : '',
        $attributes->get('class'),
    ];
@endphp

<{{ $as }} {{ $attributes->except('class')->class($classes) }}>
    {{ $slot }}
</{{ $as }}>
