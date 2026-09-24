@props([
    'title' => null,
])

@php
    $siteName = config('app.name', 'SMK Tahfizh Al-Fatih');
    $pageTitle = $title ? "{$title} — Admin {$siteName}" : "Admin {$siteName}";

    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'label' => 'Pendaftar PPDB',
            'route' => route('admin.registrations.index'),
            'active' => request()->routeIs('admin.registrations.*'),
        ],
        [
            'label' => 'Program Keahlian',
            'route' => route('admin.programs.index'),
            'active' => request()->routeIs('admin.programs.*'),
        ],
        [
            'label' => 'Berita',
            'route' => route('admin.news.index'),
            'active' => request()->routeIs('admin.news.*'),
        ],
        [
            'label' => 'Galeri',
            'route' => route('admin.galleries.index'),
            'active' => request()->routeIs('admin.galleries.*'),
        ],
        [
            'label' => 'Pengumuman',
            'route' => route('admin.announcements.index'),
            'active' => request()->routeIs('admin.announcements.*'),
        ],
        [
            'label' => 'Halaman',
            'route' => route('admin.pages.index'),
            'active' => request()->routeIs('admin.pages.*'),
        ],
        [
            'label' => 'Pesan Masuk',
            'route' => route('admin.contact-messages.index'),
            'active' => request()->routeIs('admin.contact-messages.*'),
            'badge' => \App\Models\ContactMessage::where('is_read', false)->count(),
        ],
    ];

    $secondaryNav = [
        [
            'label' => 'Pengaturan',
            'route' => route('admin.settings.index'),
            'active' => request()->routeIs('admin.settings.*'),
        ],
        [
            'label' => 'Trash',
            'route' => route('admin.trash.index'),
            'active' => request()->routeIs('admin.trash.*') || request()->routeIs('*.trash'),
        ],
    ];

    if (auth()->user()?->is_superadmin) {
        $secondaryNav[] = [
            'label' => 'Audit Log',
            'route' => route('admin.audit-logs.index'),
            'active' => request()->routeIs('admin.audit-logs.*'),
        ];
        $secondaryNav[] = [
            'label' => 'Log Login',
            'route' => route('admin.login-logs.index'),
            'active' => request()->routeIs('admin.login-logs.*'),
        ];
        $secondaryNav[] = [
            'label' => 'Kelola User',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
        ];
    }
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
            (function() {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <title>{{ $pageTitle }}</title>
        <meta name="robots" content="noindex, nofollow">

        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>

    <body class="min-h-screen bg-slate-100 dark:bg-slate-950">
        <div class="flex min-h-screen">
            {{-- Backdrop (mobile) --}}
            <div data-admin-drawer-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            {{-- Sidebar --}}
            <aside
                id="admin-sidebar"
                data-admin-drawer
                aria-label="Navigasi admin"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-950 lg:z-40 lg:translate-x-0"
            >
                <div class="flex items-center gap-2.5 px-5 py-5">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="40" height="40" class="size-10 rounded-xl object-contain" />
                    <div class="leading-tight">
                        <span class="block text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">SMK TAHFIZH</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-primary-600 dark:text-primary-400">Admin Panel</span>
                    </div>
                </div>

                <nav class="mt-2 flex-1 space-y-1 px-3 overflow-y-auto">
                    @foreach ($navItems as $item)
                        <a
                            href="{{ $item['route'] }}"
                            class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                        >
                            <span>{{ $item['label'] }}</span>
                            @if(!empty($item['badge']) && $item['badge'] > 0)
                                <span class="inline-flex size-5 items-center justify-center rounded-full bg-red-500 text-[11px] font-bold text-white">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach

                    <div class="my-3 border-t border-slate-200 dark:border-slate-800"></div>

                    @foreach ($secondaryNav as $item)
                        <a
                            href="{{ $item['route'] }}"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-slate-800 text-white dark:bg-slate-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-full bg-primary-600 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Keluar" title="Keluar">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Content --}}
            <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
                {{-- Mobile header --}}
                <header class="sticky top-0 z-30 border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:hidden">
                    <div class="flex h-14 items-center justify-between gap-2 px-4">
                        <div class="flex min-w-0 items-center gap-2">
                            <button
                                type="button"
                                data-admin-drawer-toggle
                                aria-label="Buka menu navigasi"
                                aria-expanded="false"
                                aria-controls="admin-sidebar"
                                class="-ml-1.5 inline-flex size-10 shrink-0 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                            >
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                                </svg>
                            </button>
                            <img src="{{ asset('img/logo.png') }}" alt="Logo" width="36" height="36" class="size-9 shrink-0 rounded-lg object-contain" />
                            <span class="truncate text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">ADMIN <span class="text-primary-600">AL-FATIH</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.theme-toggle />
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex shrink-0 items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                {{-- Desktop topbar --}}
                <header class="sticky top-0 z-30 hidden h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-8 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90 lg:flex">
                    <div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.theme-toggle />
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-primary-700 dark:text-slate-400 dark:hover:text-primary-400">
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 010 1.06L4.81 8.25H15a6.75 6.75 0 010 13.5h-3a.75.75 0 010-1.5h3a5.25 5.25 0 100-10.5H4.81l4.72 4.72a.75.75 0 11-1.06 1.06l-6-6a.75.75 0 010-1.06l6-6a.75.75 0 011.06 0z" clip-rule="evenodd" />
                            </svg>
                            Lihat Website
                        </a>
                    </div>
                </header>

                <main id="main-content" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    {{ $slot }}
                </main>

                <footer class="border-t border-slate-200 bg-white px-4 py-4 text-center text-xs text-slate-400 dark:border-slate-800 dark:bg-slate-950 lg:px-8">
                    &copy; {{ date('Y') }} {{ config('app.name') }} — Panel Admin
                </footer>
            </div>
        </div>

        <x-ui.toast />
        <x-ui.confirm-dialog />
        @stack('scripts')
    </body>
</html>
