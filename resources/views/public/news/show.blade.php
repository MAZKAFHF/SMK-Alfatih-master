<x-layouts.app
    :title="$news->title"
    :description="$news->excerpt"
    :image="$news->thumbnail"
    type="article"
    :published-time="$news->published_at?->toAtomString()"
    :modified-time="$news->updated_at?->toAtomString()"
    :author="$news->author?->name"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Berita', 'url' => route('news.index')],
        ['label' => $news->title, 'url' => route('news.show', $news)],
    ]"
>
    <div data-reading-progress class="fixed inset-x-0 top-0 z-50 h-0.5 origin-left bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></div>
    <x-page-hero
        variant="editorial"
        eyebrow="Berita Sekolah"
        :title="$news->title"
        :description="$news->published_at?->format('d F Y').($news->author ? ' • '.$news->author->name : '')"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Berita', 'url' => route('news.index')],
            ['label' => Str::limit($news->title, 40)],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-reveal="fade">
            @if ($news->thumbnail)
                <div data-media class="overflow-hidden rounded-2xl">
                    <img src="{{ $news->thumbnail }}" alt="{{ $news->title }}" class="aspect-video w-full object-cover" />
                </div>
            @endif

            <article class="prose-content mt-8" data-reading-article>{!! $news->content !!}</article>

            <div class="mt-12 flex items-center justify-between gap-4 border-t border-slate-200 pt-8 dark:border-slate-700/50">
                <x-ui.button variant="outline" href="{{ route('news.index') }}">
                    <span aria-hidden="true">←</span> Semua Berita
                </x-ui.button>
                <x-ui.button href="{{ route('ppdb.index') }}">Daftar PPDB</x-ui.button>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-white py-12 dark:bg-slate-950 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Berita Lainnya</h2>
                <span class="mt-3 block h-1 w-12 rounded-full bg-gold-500" aria-hidden="true"></span>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('news.show', $item) }}" class="group reveal" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                            <x-ui.card padding="false" hover="true" class="lift h-full overflow-hidden">
                                <x-thumb :src="$item->thumbnail" ratio="aspect-video" :alt="$item->title" />
                                <div class="p-5">
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $item->published_at?->format('d M Y') }}</p>
                                    <h3 class="mt-2 line-clamp-2 font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $item->title }}</h3>
                                </div>
                            </x-ui.card>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
