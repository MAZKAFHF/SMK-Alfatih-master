@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'full' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 shadow-sm focus-visible:outline-primary-600',
        'secondary' => 'bg-primary-50 text-primary-700 border border-primary-200 hover:bg-primary-100 focus-visible:outline-primary-500 dark:bg-primary-950 dark:text-primary-400 dark:border-primary-800 dark:hover:bg-primary-900',
        'outline' => 'bg-white text-slate-700 border border-slate-300 hover:border-slate-400 hover:bg-slate-50 focus-visible:outline-slate-400 shadow-sm dark:bg-transparent dark:text-slate-300 dark:border-slate-600 dark:hover:border-slate-500 dark:hover:bg-slate-800',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 focus-visible:outline-slate-400 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-sm focus-visible:outline-red-600',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600 shadow-sm focus-visible:outline-accent-500',
    ];

    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs gap-1.5',
        'sm' => 'px-3 py-2 text-sm gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-5 py-3 text-base gap-2',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center rounded-lg font-semibold transition-colors duration-150',
        'focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-60 disabled:pointer-events-none',
        'select-none whitespace-nowrap',
        $variants[$variant],
        $sizes[$size],
        $full ? 'w-full' : '',
        $attributes->get('class'),
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except('class')->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-ui.loading-spinner class="size-4" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($loading) {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-ui.loading-spinner class="size-4" />
        @endif
        {{ $slot }}
    </button>
@endif
