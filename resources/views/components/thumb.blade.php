@props([
    'src' => null,
    'alt' => '',
    'ratio' => 'aspect-video',
    'class' => '',
])

@php
    $fallback = match ($attributes->get('icon')) {
        'photo' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        'camera' => 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z',
        default => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 8.754 6 9.375v20.25m0-20.25c0-.621.504-1.125 1.125-1.125H18c.621 0 1.125.504 1.125 1.125M6 9.375v12.75m0 0c0 .621-.504 1.125-1.125 1.125M18 9.375v12.75m0 0c0 .621.504 1.125 1.125 1.125M18 9.375c0-.621-.504-1.125-1.125-1.125',
    };
@endphp

<div class="relative overflow-hidden {{ $ratio }} {{ $class }}" role="img" aria-label="{{ $alt }}">
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 hover:scale-105" />
    @else
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary-100 via-white to-accent-100 dark:from-primary-900 dark:via-slate-800 dark:to-accent-900">
            <svg class="size-10 text-primary-300 dark:text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $fallback }}" />
            </svg>
        </div>
    @endif
</div>
