@props([
    'title' => null,
    'description' => null,
    'bodyClass' => null,
    'surface' => 'future',
    'chrome' => true,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'robots' => null,
    'publishedTime' => null,
    'modifiedTime' => null,
    'author' => null,
    'breadcrumbs' => [],
])

@php
    $siteName = \App\Models\SiteSetting::get('school_name', config('app.name', 'SMK Tahfizh Al-Fatih'));
    $defaultTitle = \App\Models\SiteSetting::get('seo_title') ?: $siteName.' Pekanbaru | SMK Islam & PPDB';
    $pageTitle = $title ? "{$title} — {$siteName}" : $defaultTitle;
    $metaDescription = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags((string) ($description
            ?: \App\Models\SiteSetting::get('seo_description')
            ?: 'Website resmi SMK Tahfizh Al-Fatih Pekanbaru, SMK Islam berbasis tahfizh dengan program PPLG, Multimedia, DKV, dan TJKT serta informasi PPDB.'
        )))),
        165,
        ''
    );
    $isPrivatePage = request()->is('admin', 'admin/*', 'portal', 'portal/*', 'health', 'up', 'webhooks/*')
        || (is_numeric((string) $title) && (int) $title >= 400);
    $isIndexableHomepage = ! $isPrivatePage && request()->routeIs('home');
    $robotsContent = $isPrivatePage
        ? 'noindex, nofollow, noarchive, nosnippet'
        : ($isIndexableHomepage
            ? ($robots ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')
            : 'noindex, follow, noarchive');

    $query = request()->query();
    foreach (array_keys($query) as $key) {
        if (preg_match('/^(utm_|fbclid$|gclid$|msclkid$)/i', (string) $key)) {
            unset($query[$key]);
        }
    }
    $currentUrl = $canonical ?: url()->current().($query ? '?'.http_build_query($query) : '');

    $configuredLogo = \App\Models\SiteSetting::get('logo');
    $logoImage = ($configuredLogo ? \App\Services\MediaService::url($configuredLogo) : null) ?: asset('img/logo.png');
    $socialImage = $image ?: asset('img/beranda.png');
    if ($logoImage && !\Illuminate\Support\Str::startsWith($logoImage, ['http://', 'https://'])) {
        $logoImage = url($logoImage);
    }
    if ($socialImage && !\Illuminate\Support\Str::startsWith($socialImage, ['http://', 'https://'])) {
        $socialImage = url($socialImage);
    }

    $websiteId = route('home').'#website';
    $schoolId = route('home').'#school';
    $webpageId = $currentUrl.'#webpage';
    $schemaGraph = [[
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => route('home'),
        'name' => $siteName,
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => $schoolId],
    ], [
        '@type' => $type === 'article' ? 'NewsArticle' : 'WebPage',
        '@id' => $webpageId,
        'url' => $currentUrl,
        'name' => $pageTitle,
        'description' => $metaDescription,
        'inLanguage' => 'id-ID',
        'isPartOf' => ['@id' => $websiteId],
        'about' => ['@id' => $schoolId],
        'primaryImageOfPage' => $socialImage ? ['@type' => 'ImageObject', 'url' => $socialImage] : null,
    ]];

    if ($type === 'article') {
        $schemaGraph[1]['headline'] = (string) $title;
        $schemaGraph[1]['image'] = $socialImage ? [$socialImage] : null;
        $schemaGraph[1]['datePublished'] = $publishedTime;
        $schemaGraph[1]['dateModified'] = $modifiedTime ?: $publishedTime;
        $schemaGraph[1]['author'] = ['@type' => 'Person', 'name' => $author ?: $siteName];
        $schemaGraph[1]['publisher'] = ['@id' => $schoolId];
    }

    if (request()->routeIs('home') || (request()->routeIs('pages.show') && request()->route('slug') === 'profil')) {
        $sameAs = array_values(array_filter([
            \App\Models\SiteSetting::get('social_instagram'),
            \App\Models\SiteSetting::get('social_facebook'),
            \App\Models\SiteSetting::get('social_youtube'),
            \App\Models\SiteSetting::get('social_tiktok'),
        ]));
        $schemaGraph[] = array_filter([
            '@type' => 'HighSchool',
            '@id' => $schoolId,
            'name' => $siteName,
            'url' => route('home'),
            'logo' => ['@type' => 'ImageObject', 'url' => $logoImage],
            'description' => $metaDescription,
            'email' => \App\Models\SiteSetting::get('school_email'),
            'telephone' => \App\Models\SiteSetting::get('school_phone'),
            'address' => \App\Models\SiteSetting::get('school_address') ? [
                '@type' => 'PostalAddress',
                'streetAddress' => \App\Models\SiteSetting::get('school_address'),
                'addressCountry' => 'ID',
            ] : null,
            'sameAs' => $sameAs ?: null,
        ]);
    }

    if (count($breadcrumbs) >= 2) {
        $schemaGraph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->values()->map(fn ($item, $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'] ?? null,
            ]))->all(),
        ];
    }
    $schemaGraph = array_map(fn ($item) => array_filter($item, fn ($value) => $value !== null && $value !== ''), $schemaGraph);
    $schemaPayload = [''.'@context' => 'https://schema.org', '@graph' => $schemaGraph];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if(session('success'))<meta name="flash-success" content="{{ session('success') }}">@endif
        @if(session('error'))<meta name="flash-error" content="{{ session('error') }}">@endif
        @if(session('warning'))<meta name="flash-warning" content="{{ session('warning') }}">@endif
        @if(session('info'))<meta name="flash-info" content="{{ session('info') }}">@endif

        <script>
            (function() {
                // Pre-paint gates: theme + motion (motion hub re-asserts `js`).
                document.documentElement.classList.add('js');
                const saved = localStorage.getItem('theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $robotsContent }}">
        @unless($isPrivatePage)<link rel="canonical" href="{{ $currentUrl }}">@endunless

        @unless($isPrivatePage)
            <meta property="og:locale" content="id_ID">
            <meta property="og:type" content="{{ $type }}">
            <meta property="og:site_name" content="{{ $siteName }}">
            <meta property="og:title" content="{{ $pageTitle }}">
            <meta property="og:description" content="{{ $metaDescription }}">
            <meta property="og:url" content="{{ $currentUrl }}">
            <meta property="og:image" content="{{ $socialImage }}">
            <meta property="og:image:alt" content="{{ $title ?: $siteName }}">
            @if($publishedTime)<meta property="article:published_time" content="{{ $publishedTime }}">@endif
            @if($modifiedTime)<meta property="article:modified_time" content="{{ $modifiedTime }}">@endif

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $pageTitle }}">
            <meta name="twitter:description" content="{{ $metaDescription }}">
            <meta name="twitter:image" content="{{ $socialImage }}">
            @if($isIndexableHomepage)
                <link rel="alternate" hreflang="id-ID" href="{{ $currentUrl }}">
                <link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">
                <script type="application/ld+json">{!! json_encode($schemaPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
            @endif
        @endunless

        <meta name="theme-color" content="#047857">

        <x-site-favicon />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|sora:600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>

    <body data-page-enter data-surface="{{ $surface }}" class="{{ $surface === 'portal' ? 'portal-page' : 'future-page' }} flex min-h-screen flex-col bg-white text-slate-800 dark:bg-slate-950 dark:text-slate-200 {{ $bodyClass }}">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten utama</a>
        @if($chrome) @include('partials.navbar') @endif

        <main id="main-content" class="flex-1">
            {{ $slot }}
        </main>

        @if($chrome) @include('partials.footer') @endif

        <x-ui.toast />
        @stack('scripts')
    </body>
</html>
