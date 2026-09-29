<x-layouts.app :title="'Berita'">
    <x-page-hero
        variant="tech"
        eyebrow="Kabar Sekolah"
        title="Cerita dari Al-Fatih."
        description="Kabar terbaru seputar kegiatan, prestasi, dan informasi dari SMK Tahfizh Al-Fatih."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Berita'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($news->isEmpty())
                <x-ui.empty-state title="Belum ada berita" description="Berita akan segera hadir. Silakan kunjungi kembali." />
            @endif

            @if ($news->currentPage() === 1 && $news->isNotEmpty())
                @php $featured = $news->first(); @endphp
                <a href="{{ route('news.show', $featured) }}" class="group mb-8 grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2" data-reveal="scale">
                    <span class="block min-h-56 overflow-hidden">
                        <x-thumb :src="$featured->thumbnail" ratio="aspect-video md:aspect-auto md:h-full" :alt="$featured->title" class="h-full transition-transform duration-500 group-hover:scale-[1.03]" />
                    </span>
                    <span class="flex flex-col justify-center p-6 sm:p-8">
                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-gold-500/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400">Sorotan</span>
                        <span class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400 sm:text-3xl">{{ $featured->title }}</span>
                        <span class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $featured->excerpt }}</span>
                        <span class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $featured->published_at?->format('d M Y') }}</span>
                    </span>
                </a>
            @endif

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-stagger>
                @foreach ($news->currentPage() === 1 ? $news->skip(1) : $news as $item)
                    <a href="{{ route('news.show', $item) }}" class="group" data-stagger-item>
                        <x-ui.card padding="false" hover="true" class="lift flex h-full flex-col overflow-hidden">
                            <x-thumb :src="$item->thumbnail" ratio="aspect-video" :alt="$item->title" />
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gold-600 dark:text-gold-400">
                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->format('d M Y') }}</time>
                                    @if ($item->author)
                                        <span aria-hidden="true">·</span>
                                        <span class="text-slate-400 dark:text-slate-500">{{ $item->author->name }}</span>
                                    @endif
                                </div>
                                <h2 class="mt-2 line-clamp-2 font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $item->title }}</h2>
                                <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $item->excerpt }}</p>
                                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                                    Baca selengkapnya
                                    <svg class="size-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </x-ui.card>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $news->links() }}
            </div>
        </div>
    </section>
</x-layouts.app>
