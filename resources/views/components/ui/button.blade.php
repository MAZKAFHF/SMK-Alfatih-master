@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'full' => false,
    'shine' => false,
    'context' => null,
])

@php
    $surface = $context
        ?? (request()->routeIs('admin.*') ? 'control' : (request()->routeIs('portal.*') ? 'portal' : 'future'));

    $variantSets = [
        'control' => [
            'primary' => 'ctl-btn-primary', 'secondary' => 'ctl-btn-secondary',
            'outline' => 'ctl-btn-secondary', 'ghost' => 'ctl-btn-ghost',
            'danger' => 'ctl-btn-danger', 'accent' => 'ctl-btn-primary',
        ],
        'portal' => [
            'primary' => 'portal-btn-primary', 'secondary' => 'portal-btn-secondary',
            'outline' => 'portal-btn-secondary', 'ghost' => 'portal-btn-ghost',
            'danger' => 'portal-btn-danger', 'accent' => 'portal-btn-primary',
        ],
        'future' => [
            'primary' => 'future-btn-primary', 'secondary' => 'future-btn-secondary',
            'outline' => 'future-btn-secondary', 'ghost' => 'future-btn-ghost',
            'danger' => 'future-btn-danger', 'accent' => 'future-btn-accent',
        ],
    ];

    $base = $surface === 'control' ? 'ctl-btn' : ($surface === 'portal' ? 'portal-btn' : 'future-btn');
    $variants = $variantSets[$surface] ?? $variantSets['future'];

    $sizes = [
        'xs' => 'ctl-btn-sm',
        'sm' => 'ctl-btn-sm',
        'md' => '',
        'lg' => '!px-5 !py-3 !text-base',
    ];

    $classes = implode(' ', [
        $base,
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
