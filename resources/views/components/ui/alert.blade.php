@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
    'id' => null,
])

@php
    $tones = [
        'info' => ['var' => '--ctl-info', 'path' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'],
        'success' => ['var' => '--ctl-ok', 'path' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['var' => '--ctl-warn', 'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
        'danger' => ['var' => '--ctl-danger', 'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
    ];

    $v = $tones[$variant] ?? $tones['info'];
    $id = $id ?? 'alert-' . Str::random(6);
@endphp

<div
    id="{{ $id }}"
    role="alert"
    style="border: 1px solid color-mix(in srgb, var({{ $v['var'] }}) 35%, transparent); background: color-mix(in srgb, var({{ $v['var'] }}) 10%, var(--ctl-surface)); color: var(--ctl-text);"
    {{ $attributes->class(['flex items-start gap-3 rounded-[10px] p-4 text-sm']) }}
>
    <svg class="mt-0.5 size-5 shrink-0" style="color: var({{ $v['var'] }});" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $v['path'] }}" />
    </svg>

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            data-alert-dismiss="#{{ $id }}"
            class="shrink-0 rounded-md p-1 opacity-60 transition-opacity hover:opacity-100"
            aria-label="Tutup"
        >
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
