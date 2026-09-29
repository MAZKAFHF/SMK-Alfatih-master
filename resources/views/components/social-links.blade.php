@props(['variant' => 'footer', 'title' => 'Ikuti SMK Tahfizh Al-Fatih'])

@php
    // SINGLE SOURCE OF TRUTH: Site Settings. Fallback = owner-approved official URLs.
    $links = [
        [
            'key' => 'instagram',
            'label' => 'Instagram SMK Tahfizh Al-Fatih Pekanbaru',
            'short' => 'Instagram',
            'url' => \App\Models\SiteSetting::get('social_instagram', 'https://www.instagram.com/smktahfizhalfatihpku/'),
        ],
        [
            'key' => 'facebook',
            'label' => 'Facebook SMK Tahfizh Al-Fatih Pekanbaru',
            'short' => 'Facebook',
            'url' => \App\Models\SiteSetting::get('social_facebook', 'https://www.facebook.com/people/SmkTahfizh-AlFatih'),
        ],
        [
            'key' => 'youtube',
            'label' => 'YouTube SMK Tahfizh Al-Fatih Pekanbaru',
            'short' => 'YouTube',
            'url' => \App\Models\SiteSetting::get('social_youtube', 'https://www.youtube.com/@SMKTAHFIZHALFATIH'),
        ],
    ];
    // Kosong => sembunyikan platform (jangan render href="#" / tombol mati).
    $links = array_values(array_filter($links, fn ($l) => filled(trim((string) $l['url']))));
@endphp

@if($links !== [])
<div class="flex flex-col items-start gap-3">
    @if($title)
        <h3 @class([
            'text-sm font-semibold uppercase tracking-wider text-white' => $variant === 'footer',
            'text-sm font-semibold text-slate-900 dark:text-white' => $variant !== 'footer',
        ])>{{ $title }}</h3>
    @endif
    <div class="flex flex-row flex-wrap items-center justify-start gap-2.5" role="list" aria-label="{{ $title }}">
        @foreach($links as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
               role="listitem"
               aria-label="{{ $link['label'] }}"
               title="{{ $link['label'] }}"
               @class([
                    'social-icon inline-flex size-11 items-center justify-center rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-offset-2',
                    'border border-white/15 bg-white/5 text-slate-300 hover:border-white/25 hover:bg-white/10 hover:text-white focus-visible:outline-white' => $variant === 'footer',
                    'border border-slate-200 bg-white text-slate-500 hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-primary-600 dark:hover:text-primary-300 focus-visible:outline-primary-600' => $variant !== 'footer',
               ])>
                @if($link['key'] === 'instagram')
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="2.5" y="2.5" width="19" height="19" rx="5.5" />
                        <circle cx="12" cy="12" r="4.25" />
                        <circle cx="17.4" cy="6.6" r="1.3" fill="currentColor" stroke="none" />
                    </svg>
                @elseif($link['key'] === 'facebook')
                    <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.5 1.6-1.5h1.3V4.9c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8V11H8v3h2.5v7h3z" />
                    </svg>
                @else
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="2.5" y="5.5" width="19" height="13" rx="4" />
                        <path d="M10.5 9.75v4.5L14.75 12l-4.25-2.25z" fill="currentColor" stroke="none" />
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endif
