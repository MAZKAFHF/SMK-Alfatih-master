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
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|sora:600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>

    <body class="min-h-screen bg-slate-100 dark:bg-slate-950">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten utama</a>
        <div class="flex min-h-screen">
            {{-- Backdrop (mobile) --}}
            <div data-admin-drawer-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            {{-- Sidebar --}}
            <aside
                id="admin-sidebar"
                data-admin-drawer
                aria-label="Navigasi admin"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-white/10 bg-graphite transition-transform duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-950 lg:z-40 lg:translate-x-0"
            >
                <div class="flex items-center gap-2.5 px-5 py-5">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="40" height="40" class="size-10 rounded-xl bg-white/10 object-contain p-1" />
                    <div class="leading-tight">
                        <span class="block font-display text-sm font-extrabold tracking-tight text-white">SMK TAHFIZH</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-gold-400">Control Center</span>
                    </div>
                </div>

                <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-3">
                    @foreach ($navItems as $item)
                        @php
                            $navIcon = match($item['label']) {
                                'Dashboard' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
                                'Pendaftar PPDB' => 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75',
                                'Program Keahlian' => 'M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
                                'Berita' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M16.5 7.5h-9',
                                'Galeri' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
                                'Pengumuman' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
                                'Halaman' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                                'Pesan Masuk' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
                                default => null,
                            };
                        @endphp
                        <a
                            href="{{ $item['route'] }}"
                            class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-primary-600 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                        >
                            <span class="flex min-w-0 items-center gap-2.5">
                                @if($navIcon)
                                    <svg class="size-[18px] shrink-0 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $navIcon }}" />
                                    </svg>
                                @endif
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>
                            @if(!empty($item['badge']) && $item['badge'] > 0)
                                <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-energy-500 text-[11px] font-bold text-white">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach

                    <div class="my-3 border-t border-white/10"></div>

                    @foreach ($secondaryNav as $item)
                        <a
                            href="{{ $item['route'] }}"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="border-t border-white/10 p-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-full bg-gold-500 text-sm font-bold text-navy-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
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
