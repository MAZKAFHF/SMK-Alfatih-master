@php
    // Variant heroes per CMS slug — same route/view/controller, no backend change.
    // Sejarah has no dated milestones in CMS: undated editorial chapters only.
    $variant = match ($page->slug) {
        'sejarah' => 'story',
        'visi-misi' => 'manifesto',
        'sambutan-kepala-sekolah' => 'portrait',
        'fasilitas' => 'media',
        default => 'info',
    };
    $heroVariant = $page->slug === 'sejarah' ? 'tech' : $variant;
@endphp

<x-layouts.app
    :title="$page->meta_title ?: $page->title"
    :description="$page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 165)"
    :image="$page->image"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Profil Sekolah', 'url' => route('pages.show', 'profil')],
        ['label' => $page->title, 'url' => route('pages.show', $page->slug)],
    ]"
>
    @if($variant === 'story')
        <div data-reading-progress class="fixed inset-x-0 top-0 z-50 h-0.5 origin-left bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></div>
    @endif

    <x-page-hero
        :variant="$heroVariant"
        :eyebrow="$variant === 'manifesto' ? 'Identitas Kami' : 'Profil Sekolah'"
        :title="$page->title"
        :description="$variant === 'manifesto' ? null : $page->meta_description"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Profil', 'url' => route('pages.show', 'profil')],
            ['label' => $page->title],
        ]"
    >
        @if($variant === 'portrait' && $page->image)
            <x-slot:media>
                <div data-media class="overflow-hidden rounded-2xl shadow-soft" data-parallax="0.03">
                    <img src="{{ $page->image }}" alt="{{ $page->title }}" class="aspect-[4/5] w-full object-cover" loading="eager" />
                </div>
            </x-slot:media>
        @elseif($variant === 'media' && $page->image)
            <x-slot:media>
                <div data-media class="overflow-hidden rounded-2xl shadow-soft">
                    <img src="{{ $page->image }}" alt="{{ $page->title }}" class="aspect-video w-full object-cover" loading="eager" />
                </div>
            </x-slot:media>
        @endif
    </x-page-hero>

    @if($variant === 'manifesto')
        <section class="wash-editorial py-14 lg:py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-reveal="fade">
                <article class="prose-content manifesto-body" data-reading-article>{!! $page->content !!}</article>
                <span class="mx-auto mt-10 block h-1 w-20 rounded-full bg-gold-500" data-draw-line aria-hidden="true"></span>
            </div>
        </section>
    @elseif($variant === 'portrait')
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-reveal="fade">
                @if(!$page->image)
                    <div class="mb-8 flex items-center gap-4">
                        <span class="flex size-16 items-center justify-center rounded-2xl bg-gold-500/15 font-display text-2xl font-extrabold text-gold-600 dark:text-gold-400" aria-hidden="true">AF</span>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Kepala Sekolah</p>
                            <p class="text-sm text-slate-500">SMK Tahfizh Al-Fatih</p>
                        </div>
                    </div>
                @endif
                <article class="prose-content" data-reading-article>{!! $page->content !!}</article>
                <div class="mt-10 border-t border-slate-200 pt-6 dark:border-slate-700/50">
                    <span class="block h-1 w-16 rounded-full bg-gold-500" aria-hidden="true"></span>
                    <p class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Kepala Sekolah</p>
                    <p class="text-sm text-slate-500">SMK Tahfizh Al-Fatih Pekanbaru</p>
                </div>
                <div class="mt-8">
                    <x-ui.button variant="outline" href="{{ route('contact.index') }}">Hubungi Kami</x-ui.button>
                </div>
            </div>
        </section>
    @else
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" data-reveal="fade">
                @if($page->image && $variant !== 'media')
                    <div data-media class="mb-8 overflow-hidden rounded-2xl">
                        <img src="{{ $page->image }}" alt="{{ $page->title }}" class="aspect-video w-full object-cover" loading="lazy" />
                    </div>
                @endif
                <article class="prose-content {{ $variant === 'story' ? 'story-body' : '' }}" @if($variant === 'story') data-reading-article @endif>{!! $page->content !!}</article>
                <div class="mt-12 border-t border-slate-200 pt-8 dark:border-slate-700/50">
                    <x-ui.button variant="outline" href="{{ route('contact.index') }}">Hubungi Kami</x-ui.button>
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
