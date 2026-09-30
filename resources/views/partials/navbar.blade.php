@php
    $pages = \Illuminate\Support\Facades\Cache::remember('nav_pages', 3600, function () {
        return \App\Models\Page::published()->orderBy('order')->limit(6)->get(['title', 'slug', 'meta_description']);
    });

    $navigation = [
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Profil', 'url' => '#', 'dropdown' => $pages],
        ['label' => 'Program Keahlian', 'url' => route('programs.index')],
        ['label' => 'Berita', 'url' => route('news.index')],
        ['label' => 'Galeri', 'url' => route('gallery.index')],
        ['label' => 'Pengumuman', 'url' => route('announcements.index')],
        ['label' => 'Kontak', 'url' => route('contact.index')],
    ];

    $activeLabel = collect($navigation)->first(fn ($item) => request()->url() === $item['url'])['label'] ?? null;
    $activeSlug = request()->segments()[0] ?? '';
    $navPhone = \App\Models\SiteSetting::get('school_phone');
    $navEmail = \App\Models\SiteSetting::get('school_email');
    $navPpdb = \App\Services\PpdbAvailability::resolvePublic();
@endphp

<header id="site-header" data-navbar class="fixed inset-x-0 top-0 z-50 bg-transparent">
    @if(filled($navPhone) || filled($navEmail) || $navPpdb->portalEntryVisible())
    <div class="hidden bg-forest-900 text-emerald-50 md:block dark:bg-black/30">
        <div class="mx-auto flex h-8 max-w-7xl items-center justify-between px-6 text-[11px] font-semibold lg:px-8">
            <div class="flex items-center gap-5">
                @if(filled($navEmail))<a href="mailto:{{ $navEmail }}" class="transition-colors hover:text-gold-300">{{ $navEmail }}</a>@endif
                @if(filled($navPhone))<a href="tel:{{ preg_replace('/[^0-9+]/', '', $navPhone) }}" class="transition-colors hover:text-gold-300">{{ $navPhone }}</a>@endif
            </div>
            <div class="flex items-center gap-4 text-emerald-100/80">
                <span>Build · Character · Future</span>
                @if($navPpdb->portalEntryVisible())<a href="{{ route('portal.login') }}" class="font-bold text-gold-300 hover:text-gold-200">Portal calon siswa →</a>@endif
            </div>
        </div>
    </div>
    @endif
    <nav class="mx-auto flex h-[4.5rem] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <x-brand-logo data-navbar-logo />
            <span class="leading-tight">
                <span class="block font-display text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">SMK TAHFIZH</span>
                <span class="block text-[11px] font-semibold uppercase tracking-widest text-primary-700 dark:text-primary-400">Al-Fatih</span>
                <span class="mt-0.5 block h-0.5 w-16 rounded-full bg-gradient-to-r from-gold-500 to-energy-500" aria-hidden="true"></span>
            </span>
        </a>

        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($navigation as $item)
                @if (isset($item['dropdown']))
                    <x-ui.dropdown align="left" panelClass="!min-w-72 !p-2">
                        <x-slot:trigger>
                            <button
                                type="button"
                                class="nav-link inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ in_array($activeSlug, $item['dropdown']->pluck('slug')->all(), true) ? 'nav-active text-primary-700 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                                aria-expanded="false"
                            >
                                {{ $item['label'] }}
                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot:trigger>

                        @foreach ($item['dropdown'] as $page)
                            <a
                                href="{{ route('pages.show', $page->slug) }}"
                                role="menuitem"
                                data-dropdown-close
                                class="group flex items-start justify-between gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-primary-50 dark:hover:bg-primary-950/60 {{ request()->route('slug') === $page->slug ? 'bg-primary-50 dark:bg-primary-950/60' : '' }}"
                            >
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-slate-800 group-hover:text-primary-700 dark:text-slate-200 dark:group-hover:text-primary-300">{{ $page->title }}</span>
                                    @if ($page->meta_description)
                                        <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($page->meta_description, 64) }}</span>
                                    @endif
                                </span>
                                <svg class="mt-1 size-4 shrink-0 text-slate-300 transition-all group-hover:translate-x-0.5 group-hover:text-primary-500 dark:text-slate-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endforeach
                    </x-ui.dropdown>
                @else
                    <a
                        href="{{ $item['url'] }}"
                        class="nav-link rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $activeLabel === $item['label'] ? 'nav-active text-primary-700 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                    >{{ $item['label'] }}</a>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <x-ui.theme-toggle />
            <a href="{{ route('ppdb.index') }}" class="clip-corner-sm hidden items-center justify-center gap-1.5 bg-gradient-to-r from-energy-500 to-energy-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition duration-150 select-none whitespace-nowrap hover:from-energy-600 hover:to-energy-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-energy-500 sm:inline-flex">
                {{ $navPpdb->canRegister() ? 'Pendaftaran' : 'Informasi PPDB' }}
            </a>

            <button
                type="button"
                data-nav-toggle
                class="inline-flex size-10 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 lg:hidden"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
            >
                <svg data-nav-icon-open class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
        </div>
    </nav>

    <div data-nav-menu class="hidden max-h-[calc(100dvh-4.5rem)] overflow-y-auto border-t border-slate-200 bg-white/95 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/95 lg:hidden">
        <div class="space-y-1 px-4 py-4 sm:px-6">
            <p class="px-3 pb-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Menu Utama</p>
            @foreach ($navigation as $item)
                @if (isset($item['dropdown']))
                    <div class="px-3 pt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $item['label'] }}</div>
                    @foreach ($item['dropdown'] as $page)
                        <a
                            href="{{ route('pages.show', $page->slug) }}"
                            class="flex items-center justify-between gap-3 rounded-xl px-3 py-3 text-base font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        >{{ $page->title }}<span aria-hidden="true" class="text-slate-300 dark:text-slate-600">→</span></a>
                    @endforeach
                @else
                    <a
                        href="{{ $item['url'] }}"
                        class="flex items-center justify-between gap-3 rounded-xl px-3 py-3 text-base font-semibold transition-colors {{ $activeLabel === $item['label'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}"
                    >{{ $item['label'] }}<span aria-hidden="true" class="text-slate-300 dark:text-slate-600">→</span></a>
                @endif
            @endforeach
            <div class="px-3 pb-2 pt-4">
                <a href="{{ route('ppdb.index') }}" class="clip-corner-sm flex w-full items-center justify-center gap-1.5 bg-gradient-to-r from-energy-500 to-energy-600 px-4 py-3 text-base font-bold text-white shadow-sm">
                    {{ $navPpdb->canRegister() ? 'Pendaftaran PPDB' : 'Informasi PPDB' }}
                </a>
                <p class="mt-3 text-center text-xs text-slate-400">SMK Tahfizh Al-Fatih • Build • Character • Future</p>
            </div>
        </div>
    </div>
</header>
<div data-navbar-spacer class="h-[4.5rem] md:h-[6.5rem]" aria-hidden="true"></div>
