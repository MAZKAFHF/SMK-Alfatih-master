@props([
    'title' => null,
    'breadcrumb' => [],
])

@php
    $siteName = config('app.name', 'SMK Tahfizh Al-Fatih');
    $pageTitle = $title ? "{$title} — Admin {$siteName}" : "Admin {$siteName}";

    $navGroups = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Antrean Kerja', 'icon' => 'audit', 'route' => route('admin.work-queue.index'), 'active' => request()->routeIs('admin.work-queue.*'), 'badge' => \App\Models\PPDBRegistration::whereIn('application_status', ['submitted', 'resubmitted'])->count() + \App\Models\ContactMessage::where('handling_status', '!=', 'resolved')->where('is_archived', false)->count() + \App\Models\EmailLog::where('status', 'failed')->count()],
            ],
        ],
        [
            'label' => 'PPDB',
            'items' => [
                ['label' => 'Pendaftar PPDB', 'icon' => 'ppdb', 'route' => route('admin.registrations.index'), 'active' => request()->routeIs('admin.registrations.*')],
                ['label' => 'Slot Wawancara', 'icon' => 'slot', 'route' => route('admin.slots.index'), 'active' => request()->routeIs('admin.slots.*') || request()->routeIs('admin.appointments.*') || request()->routeIs('admin.reschedules.*')],
                ['label' => 'Periode PPDB', 'icon' => 'calendar', 'route' => route('admin.periods.index'), 'active' => request()->routeIs('admin.periods.*')],
            ],
        ],
        [
            'label' => 'Konten',
            'items' => [
                ['label' => 'Program Keahlian', 'icon' => 'program', 'route' => route('admin.programs.index'), 'active' => request()->routeIs('admin.programs.*')],
                ['label' => 'Berita', 'icon' => 'news', 'route' => route('admin.news.index'), 'active' => request()->routeIs('admin.news.*')],
                ['label' => 'Galeri', 'icon' => 'gallery', 'route' => route('admin.galleries.index'), 'active' => request()->routeIs('admin.galleries.*')],
                ['label' => 'Pengumuman', 'icon' => 'announcement', 'route' => route('admin.announcements.index'), 'active' => request()->routeIs('admin.announcements.*')],
                ['label' => 'Halaman', 'icon' => 'page', 'route' => route('admin.pages.index'), 'active' => request()->routeIs('admin.pages.*')],
            ],
        ],
        [
            'label' => 'Komunikasi',
            'items' => [
                ['label' => 'Pesan Masuk', 'icon' => 'inbox', 'route' => route('admin.contact-messages.index'), 'active' => request()->routeIs('admin.contact-messages.*'), 'badge' => \App\Models\ContactMessage::where('is_read', false)->count()],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => route('admin.settings.index'), 'active' => request()->routeIs('admin.settings.*')],
                ['label' => 'Trash', 'icon' => 'trash', 'route' => route('admin.trash.index'), 'active' => request()->routeIs('admin.trash.*') || request()->routeIs('*.trash')],
            ],
        ],
    ];

    if (auth()->user()?->is_superadmin) {
        $navGroups[] = [
            'label' => 'Superadmin',
            'items' => [
                ['label' => 'Kelola User', 'icon' => 'users', 'route' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
                ['label' => 'Audit Log', 'icon' => 'audit', 'route' => route('admin.audit-logs.index'), 'active' => request()->routeIs('admin.audit-logs.*')],
                ['label' => 'Log Login', 'icon' => 'login-log', 'route' => route('admin.login-logs.index'), 'active' => request()->routeIs('admin.login-logs.*')],
            ],
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

    <body class="ctl-page min-h-screen">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten utama</a>
        <div class="flex min-h-screen">
            {{-- Backdrop (mobile) --}}
            <div data-admin-drawer-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            {{-- Sidebar — Deep Forest di kedua tema, token side-* --}}
            <aside
                id="admin-sidebar"
                data-admin-drawer
                aria-label="Navigasi admin"
                style="background: var(--ctl-sidebar); border-right: 1px solid rgb(255 255 255 / 0.08);"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col transition-transform duration-300 ease-in-out lg:z-40 lg:translate-x-0"
            >
                <div class="flex items-center gap-2.5 px-5 pb-4 pt-5">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="40" height="40" class="size-10 rounded-xl bg-white/10 object-contain p-1" />
                    <div class="leading-tight">
                        <span class="block font-display text-sm font-extrabold tracking-tight text-white">SMK TAHFIZH AL-FATIH</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-gold-400">Control Center</span>
                    </div>
                </div>

                <nav class="ctl-scrollbar ctl-sidenav min-h-0 flex-1 space-y-0.5 overflow-y-auto px-3 pb-3" aria-label="Menu utama" data-side-nav>
                    <span data-side-indicator aria-hidden="true"></span>
                    @foreach ($navGroups as $group)
                        <p class="ctl-navgroup">{{ $group['label'] }}</p>
                        @foreach ($group['items'] as $item)
                            <x-admin.sidebar-item
                                :href="$item['route']"
                                :icon="$item['icon']"
                                :label="$item['label']"
                                :active="$item['active']"
                                :badge="$item['badge'] ?? null"
                            />
                        @endforeach
                    @endforeach
                </nav>

                <div class="shrink-0 p-3" style="border-top: 1px solid rgb(255 255 255 / 0.1);">
                    <div class="flex items-center gap-2.5 rounded-lg px-1 py-1">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gold-500 text-sm font-bold text-navy-950" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-[13px] font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[11px]" style="color: var(--ctl-side-muted);">{{ auth()->user()->is_superadmin ? 'Superadmin' : 'Admin' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="ctl-sideicon-btn" aria-label="Keluar" title="Keluar">
                                <x-admin.icon name="logout" class="size-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Content (min-w-0 agar tabel lebar tidak mendorong layout di mobile) --}}
            <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-64">
                {{-- Mobile header --}}
                <header class="sticky top-0 z-30 lg:hidden" style="background: var(--ctl-surface); border-bottom: 1px solid var(--ctl-border);">
                    <div class="flex h-14 items-center justify-between gap-2 px-4">
                        <div class="flex min-w-0 items-center gap-2">
                            <button
                                type="button"
                                data-admin-drawer-toggle
                                aria-label="Buka menu navigasi"
                                aria-expanded="false"
                                aria-controls="admin-sidebar"
                                class="ctl-btn ctl-btn-ghost -ml-1.5 !p-2"
                            >
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                                </svg>
                            </button>
                            <img src="{{ asset('img/logo.png') }}" alt="Logo" width="36" height="36" class="size-9 shrink-0 rounded-lg object-contain" />
                            <span class="truncate text-sm font-extrabold tracking-tight" style="color: var(--ctl-text);">ADMIN <span style="color: var(--ctl-primary);">AL-FATIH</span></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <x-ui.theme-toggle />
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="ctl-btn ctl-btn-ghost !px-3 !py-2">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                {{-- Desktop topbar --}}
                <header class="sticky top-0 z-30 hidden h-16 items-center justify-between px-8 backdrop-blur lg:flex" style="background: color-mix(in srgb, var(--ctl-surface) 88%, transparent); border-bottom: 1px solid var(--ctl-border);">
                    <div class="min-w-0">
                        @if(count($breadcrumb) > 0)
                            <nav aria-label="Breadcrumb" class="mb-0.5 flex items-center gap-1.5 text-xs" style="color: var(--ctl-faint);">
                                @foreach($breadcrumb as $crumb)
                                    @if(!$loop->last)
                                        @if(!empty($crumb['url']))<a href="{{ $crumb['url'] }}" class="hover:underline" style="color: var(--ctl-muted);">{{ $crumb['label'] }}</a>@else<span>{{ $crumb['label'] }}</span>@endif
                                        <span aria-hidden="true">/</span>
                                    @else
                                        <span aria-current="page" style="color: var(--ctl-text); font-weight: 600;">{{ $crumb['label'] }}</span>
                                    @endif
                                @endforeach
                            </nav>
                        @endif
                        <h1 class="truncate text-base font-bold" style="color: var(--ctl-text);">{{ $title ?? 'Control Center' }}</h1>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <x-ui.theme-toggle />
                        <a href="{{ route('home') }}" class="ctl-btn ctl-btn-ghost !px-3 !py-2">
                            Lihat Website
                        </a>
                    </div>
                </header>

                <main id="main-content" class="mx-auto w-full max-w-7xl min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    {{ $slot }}
                </main>

                <footer class="px-4 py-4 text-center text-xs lg:px-8" style="border-top: 1px solid var(--ctl-border); background: var(--ctl-surface); color: var(--ctl-faint);">
                    &copy; {{ date('Y') }} {{ config('app.name') }} — Control Center
                </footer>
            </div>
        </div>

        <x-ui.toast />
        <x-ui.confirm-dialog />
        @stack('scripts')
    </body>
</html>
