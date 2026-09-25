<x-layouts.app :title="$page->title" :description="$page->meta_description">
    <x-page-header
        :title="$page->title"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => $page->title],
        ]"
    />

    {{-- Intro editorial --}}
    <section class="py-12 lg:py-16">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="reveal">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-energy-600 dark:text-energy-500">Profil Sekolah</p>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ $page->title }}</h2>
                <span class="mt-4 block h-1 w-16 rounded-full bg-gold-500" aria-hidden="true"></span>
                <p class="mt-4 leading-relaxed text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 280) }}</p>
                <x-digital-pulse :steps="['Discover', 'Learn', 'Build']" class="mt-6 max-w-sm text-primary-700 dark:text-tech-400" />
            </div>
            <div class="reveal" style="--reveal-delay: 100ms">
                <x-thumb :src="$page->image" ratio="aspect-[4/3]" class="rounded-2xl shadow-soft" :alt="$page->title" icon="camera" />
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
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="reveal grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-card dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Jenjang</p>
                <p class="mt-1 font-display text-lg font-extrabold text-slate-900 dark:text-white">SMK</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-card dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Program Keahlian</p>
                <p class="mt-1 font-display text-lg font-extrabold text-slate-900 dark:text-white">{{ $programs->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-card dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Tahun Berdiri</p>
                <p class="mt-1 font-display text-lg font-extrabold text-slate-900 dark:text-white">{{ $foundedYear ?? '—' }}</p>
            </div>
        </div>
    </section>

    @if ($visiMisi)
        <section class="bg-navy-900 py-12 lg:py-16">
            <div class="reveal mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold-400">Visi &amp; Misi</p>
                <p class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-slate-200">{{ \Illuminate\Support\Str::limit(strip_tags($visiMisi->content), 220) }}</p>
                <x-ui.button variant="outline" size="sm" href="{{ route('pages.show', 'visi-misi') }}" class="mt-6 border-white/30 bg-white/10 text-white hover:bg-white/20">Baca Visi &amp; Misi</x-ui.button>
            </div>
        </section>
    @endif

    @if ($sambutan)
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-3xl rounded-2xl border border-gold-500/30 bg-gold-500/5 p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold-600 dark:text-gold-400">Sambutan Kepala Sekolah</p>
                <p class="mt-3 leading-relaxed text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(strip_tags($sambutan->content), 260) }}</p>
                <a href="{{ route('pages.show', 'sambutan-kepala-sekolah') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:underline dark:text-primary-400">Baca sambutan lengkap <span aria-hidden="true">→</span></a>
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
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($programs as $program)
                        <div class="reveal" style="--reveal-delay: {{ $loop->index * 80 }}ms">
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
                <h2 class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Siap bergabung dengan Al-Fatih?</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pendaftaran peserta didik baru dibuka. @if($fasilitas)<a href="{{ route('pages.show', 'fasilitas') }}" class="font-medium text-primary-700 hover:underline dark:text-primary-400">Lihat fasilitas</a> kami terlebih dahulu.@endif</p>
            </div>
            <div class="flex shrink-0 gap-3">
                <x-ui.button href="{{ route('ppdb.index') }}" class="clip-corner-sm">Daftar PPDB</x-ui.button>
                <x-ui.button variant="outline" href="{{ route('contact.index') }}">Hubungi Kami</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.app>
