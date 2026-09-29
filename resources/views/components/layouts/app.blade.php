@props([
    'title' => null,
    'description' => null,
    'bodyClass' => null,
    'surface' => 'future',
    'chrome' => true,
])

@php
    $siteName = config('app.name', 'SMK Tahfizh Al-Fatih');
    $pageTitle = $title ? "{$title} — {$siteName}" : $siteName;
    $metaDescription = $description ?? 'Website resmi SMK Tahfizh Al-Fatih. Informasi sekolah, program keahlian, berita, dan pendaftaran peserta didik baru (PPDB).';
    $currentUrl = url()->current();
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
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $currentUrl }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $currentUrl }}">

        <meta name="theme-color" content="#047857">

        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

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
