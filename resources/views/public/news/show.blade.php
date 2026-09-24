<x-layouts.app :title="$news->title" :description="$news->excerpt">
    <x-page-header
        :title="$news->title"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Berita', 'url' => route('news.index')],
            ['label' => Str::limit($news->title, 40)],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                <time datetime="{{ $news->published_at?->toIso8601String() }}" class="inline-flex items-center gap-1.5">
                    <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                    </svg>
                    {{ $news->published_at?->format('d F Y') }}
                </time>
                @if ($news->author)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                        </svg>
                        {{ $news->author->name }}
                    </span>
                @endif
            </div>

            @if ($news->thumbnail)
                <div class="mt-6">
                    <x-thumb :src="$news->thumbnail" ratio="aspect-video" class="rounded-2xl" :alt="$news->title" />
                </div>
            @endif

            <article class="prose-content mt-8">{!! $news->content !!}</article>

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
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Berita Lainnya</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('news.show', $item) }}" class="group">
                            <x-ui.card padding="false" hover="true" class="h-full overflow-hidden">
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
