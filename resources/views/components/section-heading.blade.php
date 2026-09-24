@props([
    'title' => null,
    'subtitle' => null,
    'align' => 'center',
    'actions' => null,
])

@php
    $alignments = [
        'center' => 'text-center mx-auto',
        'left' => 'text-left',
    ];
@endphp

<div class="max-w-2xl {{ $alignments[$align] }}">
    @if ($subtitle)
        <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-950 dark:text-primary-400 dark:ring-primary-800">
            <span class="size-1.5 rounded-full bg-primary-600" aria-hidden="true"></span>
            {{ $subtitle }}
        </span>
    @endif

    @if ($title)
        <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $title }}</h2>
    @endif

    @if (trim((string) $slot))
        <p class="mt-3 text-base leading-relaxed text-slate-600 dark:text-slate-400">{{ $slot }}</p>
    @endif

    @if ($actions)
        <div class="mt-6 {{ $align === 'center' ? 'flex justify-center' : '' }}">{{ $actions }}</div>
    @endif
</div>
