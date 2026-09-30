@php
    $profilePpdb = \App\Services\PpdbAvailability::resolvePublic();
@endphp
<x-layouts.app :title="$page->title" :description="$page->meta_description">
    <x-page-hero
        variant="tech"
        eyebrow="Profil Sekolah"
        :title="$page->title"
        :description="\Illuminate\Support\Str::limit(strip_tags($page->meta_description ?? $page->content), 180)"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => $page->title],
        ]"
    />

    {{-- Intro editorial --}}
    <section class="py-12 lg:py-16">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div data-reveal>
                <p class="mt-4 leading-relaxed text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 280) }}</p>
                <x-digital-pulse :steps="['Discover', 'Learn', 'Build']" class="mt-6 max-w-sm text-primary-700 dark:text-tech-400" />
            </div>
            <div data-media>
                <x-thumb :src="$page->image" ratio="aspect-[4/3]" class="rounded-2xl shadow-soft" :alt="$page->title" icon="camera" />
            </div>
        </div>
    </section>

    {{-- WHO WE ARE — hub identitas --}}
    <section class="wash-offwhite py-12 lg:py-16" aria-label="Jelajahi profil sekolah">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl" data-stagger>
                <p data-stagger-item class="text-xs font-bold uppercase tracking-[0.25em] text-energy-600 dark:text-energy-500">Who We Are</p>
                <h2 data-stagger-item class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Mengenal Al-Fatih Lebih Dekat.</h2>
            </div>
            <div class="mt-8 divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800" data-stagger>
                @php
                    $hub = array_filter([
                        ['no' => '01', 'title' => 'Profil Sekolah', 'desc' => 'Identitas, jenjang, dan program.', 'url' => '#identitas', 'img' => $page->image],
                        $sejarah ? ['no' => '02', 'title' => 'Sejarah', 'desc' => \Illuminate\Support\Str::limit(strip_tags($sejarah->meta_description ?? $sejarah->content), 70), 'url' => route('pages.show', 'sejarah'), 'img' => $sejarah->image] : null,
                        $visiMisi ? ['no' => '03', 'title' => 'Visi & Misi', 'desc' => \Illuminate\Support\Str::limit(strip_tags($visiMisi->meta_description ?? $visiMisi->content), 70), 'url' => route('pages.show', 'visi-misi'), 'img' => $visiMisi->image] : null,
                        $sambutan ? ['no' => '04', 'title' => 'Sambutan Kepala Sekolah', 'desc' => \Illuminate\Support\Str::limit(strip_tags($sambutan->meta_description ?? $sambutan->content), 70), 'url' => route('pages.show', 'sambutan-kepala-sekolah'), 'img' => $sambutan->image] : null,
                        $fasilitas ? ['no' => '05', 'title' => 'Fasilitas', 'desc' => \Illuminate\Support\Str::limit(strip_tags($fasilitas->meta_description ?? $fasilitas->content), 70), 'url' => route('pages.show', 'fasilitas'), 'img' => $fasilitas->image] : null,
                    ]);
                @endphp
                @foreach ($hub as $item)
                    <a href="{{ $item['url'] }}" class="group flex items-center gap-5 py-5 sm:gap-8 sm:py-6" data-stagger-item data-spotlight>
                        <span class="font-display text-sm font-extrabold text-slate-300 transition-colors group-hover:text-primary-500 dark:text-slate-600" aria-hidden="true">{{ $item['no'] }}</span>
                        @if($item['img'])
                            <span class="hidden size-16 shrink-0 overflow-hidden rounded-xl sm:block" aria-hidden="true">
                                <img src="{{ $item['img'] }}" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            </span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block font-display text-xl font-extrabold tracking-tight text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400 sm:text-2xl">{{ $item['title'] }}</span>
                            <span class="mt-1 block truncate text-sm text-slate-500 dark:text-slate-400">{{ $item['desc'] }}</span>
                        </span>
                        <svg class="size-6 shrink-0 text-slate-300 transition-all group-hover:translate-x-1.5 group-hover:text-primary-500 dark:text-slate-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Isi profil --}}
    <section class="bg-white py-12 dark:bg-slate-950 lg:py-16">
        <div class="reveal mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <article class="prose-content">{!! $page->content !!}</article>
        </div>
    </section>

    {{-- Identitas ringkas (data real) --}}
    <section id="identitas" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl" data-stagger>
            <p data-stagger-item class="text-xs font-bold uppercase tracking-[0.25em] text-energy-600 dark:text-energy-500">Identitas Sekolah</p>
            <h2 data-stagger-item class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Sekolah Kejuruan Berbasis Tahfizh.</h2>
        </div>
        <dl class="mt-8 divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800" data-stagger>
            @foreach ([
                ['label' => 'Jenjang', 'value' => 'SMK (Sekolah Menengah Kejuruan)'],
                ['label' => 'Program Keahlian', 'value' => $programs->count().' program aktif'],
                ['label' => 'Tahun Berdiri', 'value' => $foundedYear ?? '—'],
            ] as $row)
                <div data-stagger-item class="group flex items-baseline justify-between gap-6 py-4 transition-colors hover:bg-primary-50/50 dark:hover:bg-primary-950/30">
                    <dt class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 transition-colors group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $row['label'] }}</dt>
                    <dd class="text-right font-display text-lg font-extrabold text-slate-900 dark:text-white sm:text-xl">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    @if ($visiMisi)
        <section class="bg-navy-900 py-12 lg:py-16" data-spotlight>
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8" data-stagger>
                <p data-stagger-item class="text-xs font-bold uppercase tracking-[0.25em] text-gold-400">Visi &amp; Misi</p>
                <p data-stagger-item class="mx-auto mt-4 max-w-2xl font-display text-xl font-bold leading-relaxed text-white sm:text-2xl">“{{ \Illuminate\Support\Str::limit(strip_tags($visiMisi->content), 220) }}”</p>
                <div data-stagger-item><x-ui.button variant="outline" size="sm" href="{{ route('pages.show', 'visi-misi') }}" class="mt-6 border-white/30 bg-white/10 text-white hover:bg-white/20">Baca Visi &amp; Misi</x-ui.button></div>
            </div>
        </section>
    @endif

    @if ($sambutan)
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl rounded-2xl border border-gold-500/30 bg-gold-500/5 p-6 sm:p-8" data-reveal="scale">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold-600 dark:text-gold-400">Sambutan Kepala Sekolah</p>
                <p class="mt-3 leading-relaxed text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(strip_tags($sambutan->content), 260) }}</p>
                <a href="{{ route('pages.show', 'sambutan-kepala-sekolah') }}" class="link-underline mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-700 dark:text-primary-400">Baca sambutan lengkap <span aria-hidden="true">→</span></a>
            </div>
        </section>
    @endif

    @if ($programs->isNotEmpty())
        <section class="bg-white py-12 dark:bg-slate-950 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="reveal flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-energy-600">Program Keahlian</p>
                        <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Belajar Sesuai Bakatmu</h2>
                    </div>
                    <x-ui.button variant="outline" size="sm" href="{{ route('programs.index') }}">Semua Program</x-ui.button>
                </div>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-stagger>
                    @foreach ($programs as $program)
                        <div data-stagger-item>
                            <x-program-card :program="$program" :tone="['emerald', 'orange', 'gold', 'navy'][$loop->index % 4]" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:p-8">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Kenali proses bergabung dengan Al-Fatih.</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $profilePpdb->canRegister() ? 'Pendaftaran peserta didik baru sedang dibuka.' : 'Jadwal dan status PPDB tersedia pada halaman informasi resmi.' }}
                    @if($fasilitas)
                        <a href="{{ route('pages.show', 'fasilitas') }}" class="font-medium text-primary-700 hover:underline dark:text-primary-400">Lihat fasilitas</a> kami terlebih dahulu.
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 gap-3">
                <x-ui.button href="{{ route('ppdb.index') }}" class="clip-corner-sm">{{ $profilePpdb->canRegister() ? 'Daftar PPDB' : 'Informasi PPDB' }}</x-ui.button>
                <x-ui.button variant="outline" href="{{ route('contact.index') }}">Hubungi Kami</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.app>
