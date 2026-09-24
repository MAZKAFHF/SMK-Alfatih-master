<x-layouts.app :title="'Berita'">
    <x-page-header
        title="Berita Sekolah"
        subtitle="Kabar terbaru seputar kegiatan, prestasi, dan informasi dari SMK Tahfizh Al-Fatih."
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

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($news as $item)
                    <a href="{{ route('news.show', $item) }}" class="group">
                        <x-ui.card padding="false" hover="true" class="flex h-full flex-col overflow-hidden">
                            <x-thumb :src="$item->thumbnail" ratio="aspect-video" :alt="$item->title" />
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->format('d M Y') }}</time>
                                    @if ($item->author)
                                        <span aria-hidden="true">·</span>
                                        <span>{{ $item->author->name }}</span>
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
