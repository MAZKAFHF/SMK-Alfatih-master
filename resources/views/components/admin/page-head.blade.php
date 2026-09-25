@props([
    'title' => null,
    'context' => null,
])

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
        @if ($context)
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $context }}</p>
        @endif
    </div>
    @if (trim((string) $slot) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
