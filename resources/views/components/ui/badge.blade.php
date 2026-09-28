@props([
    'color' => 'primary',
    'dot' => false,
    'size' => 'md',
])

@php
    // Nama warna lama dipetakan ke 5 status semantik ALFATIH//CONTROL.
    $map = [
        'primary' => 'info', 'accent' => 'warning',
        'green' => 'success', 'emerald' => 'success',
        'blue' => 'info', 'sky' => 'info',
        'amber' => 'warning', 'orange' => 'warning', 'gold' => 'warning',
        'red' => 'danger',
        'purple' => 'info', 'violet' => 'info',
        'slate' => 'neutral', 'navy' => 'neutral',
        'success' => 'success', 'warning' => 'warning',
        'danger' => 'danger', 'info' => 'info', 'neutral' => 'neutral',
    ];
    $tone = $map[$color] ?? 'neutral';
@endphp

<span {{ $attributes->class(['ctl-badge', 'ctl-badge-'.$tone]) }}>
    @if ($dot)
        <span class="dot" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
