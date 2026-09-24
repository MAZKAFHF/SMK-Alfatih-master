@props([
    'href' => null,
    'active' => false,
    'danger' => false,
])

@php
    $classes = [
        'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm transition-colors',
        $danger
            ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950'
            : ($active ? 'bg-primary-50 text-primary-700 font-medium dark:bg-primary-950 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white'),
        $attributes->get('class'),
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" data-dropdown-close role="menuitem" {{ $attributes->except('class')->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" data-dropdown-close role="menuitem" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        {{ $slot }}
    </button>
@endif
