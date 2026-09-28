@props([
    'title' => null,
    'context' => null,
])

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="ctl-page-title">{{ $title }}</h1>
        @if ($context)
            <p class="ctl-muted mt-1 text-sm">{{ $context }}</p>
        @endif
    </div>
    @if (trim((string) $slot) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
