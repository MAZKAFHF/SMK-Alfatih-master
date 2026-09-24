@props([
    'text' => 'Memuat...',
    'size' => 'md',
    'inline' => false,
])

@php
    $sizes = [
        'sm' => 'size-4',
        'md' => 'size-6',
        'lg' => 'size-8',
    ];
@endphp

@if ($inline)
    <span {{ $attributes->class(['inline-flex items-center gap-2 text-sm text-slate-500']) }} role="status" aria-live="polite">
        <x-ui.loading-spinner class="{{ $sizes[$size] }}" />
        {{ $text }}
    </span>
@else
    <div {{ $attributes->class(['flex flex-col items-center justify-center gap-3 py-12']) }} role="status" aria-live="polite">
        <x-ui.loading-spinner class="{{ $sizes[$size] }} text-primary-600" />
        <p class="text-sm text-slate-500">{{ $text }}</p>
    </div>
@endif
