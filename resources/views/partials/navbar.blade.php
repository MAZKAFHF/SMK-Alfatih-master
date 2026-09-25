@php
    $pages = \Illuminate\Support\Facades\Cache::remember('nav_pages', 3600, function () {
        return \App\Models\Page::published()->orderBy('order')->limit(6)->get(['title', 'slug']);
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
@endphp

<header id="site-header" data-navbar class="sticky top-0 z-40 bg-transparent">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="40" height="40" class="size-10 rounded-xl object-contain shadow-sm" />
            <span class="leading-tight">
                <span class="block font-display text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">SMK TAHFIZH</span>
                <span class="block text-[11px] font-semibold uppercase tracking-widest text-primary-700 dark:text-primary-400">Al-Fatih</span>
                <span class="mt-0.5 block h-0.5 w-16 rounded-full bg-gradient-to-r from-gold-500 to-energy-500" aria-hidden="true"></span>
            </span>
        </a>

        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($navigation as $item)
                @if (isset($item['dropdown']))
                    <x-ui.dropdown>
                        <x-slot:trigger>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ in_array($activeSlug, $item['dropdown']->pluck('slug')->all(), true) ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                                aria-expanded="false"
                            >
                                {{ $item['label'] }}
                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot:trigger>

                        @foreach ($item['dropdown'] as $page)
                            <x-ui.dropdown-item :href="route('pages.show', $page->slug)" :active="request()->route('slug') === $page->slug">
                                {{ $page->title }}
                            </x-ui.dropdown-item>
                        @endforeach
                    </x-ui.dropdown>
                @else
                    <a
                        href="{{ $item['url'] }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $activeLabel === $item['label'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                    >{{ $item['label'] }}</a>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <x-ui.theme-toggle />
            <a href="{{ route('ppdb.index') }}" class="clip-corner-sm hidden items-center justify-center gap-1.5 bg-gradient-to-r from-energy-500 to-energy-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition duration-150 select-none whitespace-nowrap hover:from-energy-600 hover:to-energy-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-energy-500 sm:inline-flex">
                Pendaftaran
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

    <div data-nav-menu class="hidden max-h-[80vh] overflow-y-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($navigation as $item)
                @if (isset($item['dropdown']))
                    <div class="px-3 pt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $item['label'] }}</div>
                    @foreach ($item['dropdown'] as $page)
                        <a
                            href="{{ route('pages.show', $page->slug) }}"
                            class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        >{{ $page->title }}</a>
                    @endforeach
                @else
                    <a
                        href="{{ $item['url'] }}"
                        class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $activeLabel === $item['label'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}"
                    >{{ $item['label'] }}</a>
                @endif
            @endforeach
            <a href="{{ route('ppdb.index') }}" class="block px-3 py-2.5 sm:hidden">
                <x-ui.button variant="primary" size="sm" full="true">Pendaftaran</x-ui.button>
            </a>
        </div>
    </div>
</header>
