@props([
    'as' => 'div',
    'padding' => true,
    'hover' => false,
    'context' => null,
])

@php
    $surface = $context
        ?? (request()->routeIs('admin.*') ? 'control' : (request()->routeIs('portal.*') ? 'portal' : 'future'));
    $base = match ($surface) {
        'control' => 'ctl-card',
        'portal' => 'portal-card',
        default => 'future-card',
    };
    $classes = [
        $base,
        $padding ? 'p-6' : '',
        $hover ? 'card-interactive' : '',
        $attributes->get('class'),
    ];
@endphp

<{{ $as }} {{ $attributes->except('class')->class($classes) }}>
    {{ $slot }}
</{{ $as }}>
