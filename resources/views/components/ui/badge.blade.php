@props([
    'color' => 'primary',
    'dot' => false,
    'size' => 'md',
])

@php
    $colors = [
        'primary' => 'bg-primary-50 text-primary-700 ring-primary-200 dark:bg-primary-950 dark:text-primary-400 dark:ring-primary-800',
        'accent' => 'bg-accent-50 text-accent-700 ring-accent-200 dark:bg-accent-950 dark:text-accent-400 dark:ring-accent-800',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700',
        'red' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950 dark:text-red-400 dark:ring-red-800',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-400 dark:ring-emerald-800',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-950 dark:text-sky-400 dark:ring-sky-800',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:ring-amber-800',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-950 dark:text-purple-400 dark:ring-purple-800',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center gap-1.5 rounded-full font-medium ring-1 ring-inset whitespace-nowrap',
        $colors[$color],
        $sizes[$size],
    ]) }}
>
    @if ($dot)
        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
