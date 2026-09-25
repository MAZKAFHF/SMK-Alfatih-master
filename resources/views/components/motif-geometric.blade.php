@props([
    'class' => '',
])

{{-- Motif geometris 8-titik Islami halus — tanda tangan visual ALFATIH//FUTURE --}}
<svg class="pointer-events-none {{ $class }}" viewBox="0 0 120 120" fill="none" aria-hidden="true">
    <defs>
        <pattern id="afgeo-{{ md5($class) }}" width="60" height="60" patternUnits="userSpaceOnUse">
            <g stroke="currentColor" stroke-width="1" fill="none">
                <rect x="17" y="17" width="26" height="26" />
                <rect x="17" y="17" width="26" height="26" transform="rotate(45 30 30)" />
                <circle cx="30" cy="30" r="2.5" fill="currentColor" stroke="none" />
            </g>
        </pattern>
    </defs>
    <rect width="120" height="120" fill="url(#afgeo-{{ md5($class) }})" />
</svg>
