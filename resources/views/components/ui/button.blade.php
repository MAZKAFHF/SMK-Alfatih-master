@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'full' => false,
    'shine' => false,
])

@php
    // API lama dipertahankan; visual dari token ALFATIH//CONTROL (ctl-btn).
    $variants = [
        'primary' => 'ctl-btn-primary',
        'secondary' => 'ctl-btn-secondary',
        'outline' => 'ctl-btn-secondary',
        'ghost' => 'ctl-btn-ghost',
        'danger' => 'ctl-btn-danger',
        'accent' => 'ctl-btn-primary',
    ];

    $sizes = [
        'xs' => 'ctl-btn-sm',
        'sm' => 'ctl-btn-sm',
        'md' => '',
        'lg' => '!px-5 !py-3 !text-base',
    ];

    $classes = implode(' ', [
        'ctl-btn',
        $variants[$variant] ?? 'ctl-btn-primary',
        $sizes[$size] ?? '',
        $full ? 'w-full' : '',
        $shine ? 'btn-shine' : '',
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
