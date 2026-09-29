@props(['title' => null])

@php
    $siteName = config('app.name', 'SMK Tahfizh Al-Fatih');
    $pageTitle = $title ? "{$title} — Portal {$siteName}" : "Portal — {$siteName}";
    $portalNav = [
        ['label' => 'Beranda', 'route' => route('portal.dashboard'), 'active' => request()->routeIs('portal.dashboard')],
        ['label' => 'Pendaftaran', 'route' => route('portal.applications.create'), 'active' => request()->routeIs('portal.applications.*')],
        ['label' => 'Notifikasi', 'route' => route('portal.notifications'), 'active' => request()->routeIs('portal.notifications')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if(session('success'))<meta name="flash-success" content="{{ session('success') }}">@endif
        @if(session('error'))<meta name="flash-error" content="{{ session('error') }}">@endif
        @if(session('warning'))<meta name="flash-warning" content="{{ session('warning') }}">@endif
        @if(session('info'))<meta name="flash-info" content="{{ session('info') }}">@endif
        <script>
            (function () {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) document.documentElement.classList.add('dark');
            })();
        </script>
        <title>{{ $pageTitle }}</title>
        <meta name="robots" content="noindex, nofollow">
        <meta name="theme-color" content="#075e47">
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|sora:600,700,800" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body data-surface="portal" class="portal-page flex min-h-screen flex-col antialiased">
        <a href="#portal-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-primary-700 focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white">Lewati ke konten portal</a>

        <header class="sticky top-0 z-40 border-b border-emerald-950/10 bg-white/88 backdrop-blur-xl dark:border-white/10 dark:bg-[#07120e]/88">
            <div class="mx-auto flex h-[4.5rem] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('portal.dashboard') }}" class="flex min-w-0 items-center gap-3" aria-label="Beranda Portal PPDB">
                    <img src="{{ asset('img/logo.png') }}" alt="" width="42" height="42" class="size-10 shrink-0 rounded-xl bg-white object-contain p-0.5 shadow-sm">
                    <span class="min-w-0 leading-none">
                        <span class="block truncate font-display text-sm font-extrabold tracking-tight text-slate-950 dark:text-white">ALFATIH<span class="text-primary-700 dark:text-tech-400">//PORTAL</span></span>
                        <span class="mt-1 block truncate text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Ruang Calon Siswa</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi portal">
                    @foreach($portalNav as $item)
                        <a href="{{ $item['route'] }}" @class([
                            'rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors',
                            'bg-primary-700 text-white shadow-sm' => $item['active'],
                            'text-slate-600 hover:bg-emerald-50 hover:text-primary-800 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$item['active'],
                        ]) @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1.5">
                    <x-ui.theme-toggle />
                    <div class="hidden text-right leading-tight sm:block">
                        <p class="max-w-40 truncate text-xs font-bold text-slate-800 dark:text-slate-100">{{ auth()->user()?->name }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Akun pemohon</p>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex size-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-red-50 hover:text-red-700 dark:text-slate-400 dark:hover:bg-red-950/30 dark:hover:text-red-300" aria-label="Keluar dari portal" title="Keluar">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" /></svg>
                        </button>
                    </form>
                </div>
            </div>

            <nav class="no-scrollbar flex gap-1 overflow-x-auto border-t border-emerald-950/5 px-4 py-2 md:hidden dark:border-white/5" aria-label="Navigasi portal seluler">
                @foreach($portalNav as $item)
                    <a href="{{ $item['route'] }}" @class([
                        'shrink-0 rounded-lg px-3 py-2 text-xs font-bold',
                        'bg-primary-700 text-white' => $item['active'],
                        'text-slate-600 dark:text-slate-300' => !$item['active'],
                    ]) @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </header>

        <main id="portal-content" class="mx-auto w-full max-w-7xl flex-1 px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
            @if(session('success'))<div class="mb-5"><x-ui.alert variant="success" :title="session('success')" dismissible>{{ session('success') }}</x-ui.alert></div>@endif
            @if(session('error'))<div class="mb-5"><x-ui.alert variant="danger" title="Terjadi kendala" dismissible>{{ session('error') }}</x-ui.alert></div>@endif
            @if(session('info'))<div class="mb-5"><x-ui.alert variant="info" title="Informasi" dismissible>{{ session('info') }}</x-ui.alert></div>@endif
            {{ $slot }}
        </main>

        <footer class="border-t border-emerald-950/10 bg-white/60 px-4 py-4 text-center text-xs text-slate-500 dark:border-white/10 dark:bg-black/10 dark:text-slate-400">
            Portal resmi {{ $siteName }} · Bantuan tersedia melalui halaman kontak sekolah
        </footer>
        <x-ui.toast />
        <x-ui.confirm-dialog />
        @stack('scripts')
    </body>
</html>
