@props([
    'text' => null,
])

{{-- Tooltip ALFATIH (gaya ctl, bukan title bawaan). Jangan dipakai berlebihan. --}}
<span data-ctl-tooltip="{{ $text }}" tabindex="0" {{ $attributes->class(['inline-flex items-center']) }}>
    {{ $slot }}
</span>
