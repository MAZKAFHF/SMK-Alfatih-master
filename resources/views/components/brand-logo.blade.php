@props([
    'size' => 'md',
    'alt' => 'Logo SMK Tahfizh Al-Fatih',
    'decorative' => false,
])

@php
    $sizes = [
        'sm' => ['frame' => 'size-9 rounded-lg p-1.5', 'pixels' => 36],
        'md' => ['frame' => 'size-11 rounded-xl p-1.5', 'pixels' => 44],
        'lg' => ['frame' => 'size-18 rounded-2xl p-2', 'pixels' => 72],
    ];
    $selectedSize = $sizes[$size] ?? $sizes['md'];
    $configuredLogo = \App\Models\SiteSetting::get('logo');
    $logoUrl = \App\Services\MediaService::url($configuredLogo) ?: asset('img/logo.png');
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center overflow-hidden bg-white shadow-sm ring-1 ring-slate-200/80 dark:ring-white/15',
    $selectedSize['frame'],
]) }}>
    <img
        src="{{ $logoUrl }}"
        alt="{{ $decorative ? '' : $alt }}"
        width="{{ $selectedSize['pixels'] }}"
        height="{{ $selectedSize['pixels'] }}"
        class="block h-full w-full object-contain object-center"
        @if($decorative) aria-hidden="true" @endif
    />
</span>
