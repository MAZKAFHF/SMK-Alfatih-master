<x-layouts.app>
    {{-- HERO — video identitas sekolah dengan zona logo yang tetap bersih --}}
    <section class="relative overflow-hidden bg-navy-950 text-white" data-hero>
        <div class="absolute inset-0 overflow-hidden bg-navy-950" aria-hidden="true">
            <video
                data-ambient-video
                class="hero-flag-video absolute inset-0 size-full object-cover"
                src="{{ asset('video/smk-motion-v2.mp4') }}"
                poster="{{ asset('video/smk-motion-poster.webp') }}"
                muted
                loop
                autoplay
                playsinline
                preload="metadata"
            ></video>
            <div class="pointer-events-none absolute inset-0 bg-navy-950/35"></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-navy-950/95 via-navy-950/65 to-navy-950/10"></div>
        </div>

        <div class="relative mx-auto flex min-h-[calc(100svh-4.5rem)] max-w-7xl items-center px-4 py-12 sm:px-6 md:min-h-[calc(100svh-6.5rem)] md:py-16 lg:px-8">
            <div class="w-full max-w-xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-50 ring-1 ring-inset ring-white/20 backdrop-blur-sm" data-hero-item style="--hero-delay: 0ms">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    PPDB {{ $ppdbState->period?->academic_year ?? '' }} {{ $ppdbState->publicLabel() }}
                </div>

                <h1 class="mt-6 text-xs font-bold uppercase tracking-[0.25em] text-gold-400" data-hero-item style="--hero-delay: 90ms">SMK Tahfizh Al-Fatih Pekanbaru</h1>
                <div class="mt-3 font-display text-[2rem] font-extrabold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-[2.65rem] xl:text-5xl">
                    <span data-mask-line style="--reveal-delay: 140ms"><span>Membangun Generasi</span></span>
                    <span data-mask-line style="--reveal-delay: 230ms"><span class="text-tech-300">Berilmu.</span></span>
                    <span data-mask-line style="--reveal-delay: 320ms"><span><span class="word-swap text-gold-400" data-word-swap data-words='["Berkarakter.", "Terampil.", "Siap Berkarya."]'><span>Berkarakter.</span></span></span></span>
                </div>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-200 lg:text-lg" data-hero-item style="--hero-delay: 320ms">
                    {{ $schoolTagline ?: 'Sekolah menengah kejuruan berbasis tahfizh Al-Qur’an untuk belajar, berkarya, dan bertumbuh.' }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row" data-hero-item style="--hero-delay: 410ms">
                    @if($ppdbState->canRegister())
                    <x-ui.button size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm" shine data-magnetic>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        Daftar PPDB
                    </x-ui.button>
                    @else
                    <x-ui.button size="lg" variant="outline" href="{{ route('ppdb.index') }}" class="clip-corner-sm border-white/30 bg-white/10 text-white hover:border-white/40 hover:bg-white/20" data-magnetic>Lihat Informasi PPDB</x-ui.button>
                    @endif
                    <x-ui.button size="lg" variant="outline" href="{{ route('pages.show', 'profil') }}" class="border-white/30 bg-white/10 text-white hover:border-white/40 hover:bg-white/20" data-magnetic>Lihat Profil Sekolah</x-ui.button>
                </div>

                <x-digital-pulse :steps="['Learn', 'Build', 'Impact']" class="mt-10 max-w-md text-tech-300" />
            </div>
        </div>

        <a href="#tentang" class="scroll-cue absolute bottom-14 right-8 hidden flex-col items-center gap-2 text-white/70 transition-colors hover:text-gold-400 lg:flex" aria-label="Gulir ke bawah">
            <span class="text-[11px] font-semibold uppercase tracking-[0.2em]">Gulir</span>
            <span class="scroll-cue-line block h-8 w-px bg-current" aria-hidden="true"></span>
        </a>

        <div class="relative border-t border-white/10 bg-navy-950/85 py-3 backdrop-blur">
            <x-marquee-strip :items="['Kompetensi Keahlian', 'Project-Based Learning', 'Tahfizh & Karakter', 'Siap Berkarya']" class="text-slate-200" />
        </div>
    </section>

    {{-- Statistik --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <x-stat-ribbon :stats="[
            ['label' => 'Program Keahlian', 'value' => $stats['programs']],
            ['label' => 'Tahun Berdiri', 'value' => $stats['founded']],
            ['label' => 'Siswa Aktif', 'value' => $stats['students']],
            ['label' => 'Alumni', 'value' => $stats['alumni']],
        ]" />
    </section>

    {{-- Tentang --}}
    <section id="tentang" class="scroll-mt-20 bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="reveal">
                <x-section-heading align="left" subtitle="Tentang Kami" title="SMK Tahfizh Al-Fatih di Pekanbaru">
                    SMK Tahfizh Al-Fatih Pekanbaru adalah sekolah menengah kejuruan Islam yang memadukan pendidikan vokasi modern dengan pembinaan hafalan Al-Qur'an. Siswa belajar melalui praktik, proyek, dan pembentukan karakter agar berilmu, terampil, siap berkarya, serta kokoh dalam iman dan akhlak.
                </x-section-heading>

                <ul class="mt-8 space-y-4" data-stagger>
                    @foreach ([
                        ['title' => 'Jurusan PPLG dan TJKT', 'desc' => 'Program keahlian menjadi ruang siswa mengembangkan kompetensi teknologi, jaringan, dan karya.'],
                        ['title' => 'Program Tahfizh Al-Qur’an', 'desc' => 'Pembinaan hafalan Al-Qur’an hadir sebagai bagian dari identitas pendidikan sekolah Islam.'],
                        ['title' => 'PPDB SMK Pekanbaru', 'desc' => 'Informasi dan pendaftaran peserta didik baru tersedia secara online melalui portal resmi sekolah.'],
                    ] as $feature)
                        <li data-stagger-item class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400" aria-hidden="true">
                                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $feature['title'] }}</p>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $feature['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    <x-ui.button variant="outline" href="{{ route('pages.show', 'profil') }}">Baca Selengkapnya</x-ui.button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4" data-stagger data-parallax="0.03">
                @foreach($galleries->take(4) as $gallery)
                    <div data-stagger-item @class(['mt-8' => $loop->index === 1, '-mt-8' => $loop->index === 2])>
                        <x-thumb :src="$gallery->image" ratio="aspect-[3/4]" class="rounded-xl" :alt="$gallery->title" />
                    </div>
                @endforeach
                @if($galleries->isEmpty())
                    <div class="col-span-2 flex aspect-[4/3] items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900">Dokumentasi sekolah akan hadir di sini.</div>
                @endif
            </div>
        </div>
    </section>

    @if($sambutan)
    {{-- Sambutan — institutional warmth with ALFATIH//FUTURE composition --}}
    <section class="relative overflow-hidden border-y border-slate-200/70 bg-[#f7f5ef] py-16 dark:border-slate-800 dark:bg-slate-900/50 lg:py-24" aria-labelledby="headmaster-welcome">
        <div class="pointer-events-none absolute right-0 top-0 font-display text-[18vw] font-extrabold leading-none text-forest-900/[0.035] dark:text-white/[0.025]" aria-hidden="true">AF</div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 md:grid-cols-[0.78fr_1.22fr] lg:gap-16 lg:px-8">
            <div class="relative mx-auto w-full max-w-sm" data-reveal="scale">
                <div class="absolute -left-4 -top-4 h-24 w-24 border-l-2 border-t-2 border-gold-500" aria-hidden="true"></div>
                <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem_1.5rem_4rem_1.5rem] bg-forest-900 shadow-[0_24px_70px_rgba(6,62,50,0.22)]" data-media>
                    @if($sambutan->image)
                        <img src="{{ $sambutan->image }}" alt="{{ $sambutan->title }}" loading="lazy" class="h-full w-full object-cover">
                    @else
                        <div class="tech-grid flex h-full items-center justify-center"><span class="font-display text-7xl font-extrabold text-white/15">AF</span></div>
                    @endif
                </div>
                <div class="absolute -bottom-4 -right-3 max-w-[85%] rounded-xl border border-white/70 bg-white/95 px-4 py-3 shadow-soft backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                    <p class="truncate text-sm font-extrabold text-slate-900 dark:text-white">{{ $headmasterName ?: 'Kepala Sekolah' }}</p>
                    <p class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.16em] text-primary-700 dark:text-tech-400">SMK Tahfizh Al-Fatih</p>
                </div>
            </div>

            <div data-stagger>
                <p data-stagger-item class="future-kicker">Suara dari sekolah</p>
                <h2 data-stagger-item id="headmaster-welcome" class="mt-4 max-w-2xl font-display text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl">Menyambut setiap langkah menuju masa depan.</h2>
                <svg data-stagger-item class="mt-6 size-8 text-gold-500" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M13.5 7C8 9.4 5 13.3 5 18.8 5 23.5 7.5 26 11 26c3.2 0 5.5-2.3 5.5-5.3 0-2.8-2-4.8-4.7-4.8-.8 0-1.5.1-2 .4.8-2.6 2.7-4.7 5.7-6.3L13.5 7zm13 0C21 9.4 18 13.3 18 18.8c0 4.7 2.5 7.2 6 7.2 3.2 0 5.5-2.3 5.5-5.3 0-2.8-2-4.8-4.7-4.8-.8 0-1.5.1-2 .4.8-2.6 2.7-4.7 5.7-6.3L26.5 7z" /></svg>
                <p data-stagger-item class="mt-3 max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-300 sm:text-lg">{{ \Illuminate\Support\Str::limit(trim(strip_tags($sambutan->content)), 330) }}</p>
                <div data-stagger-item class="mt-7"><x-ui.button variant="outline" href="{{ route('pages.show', 'sambutan-kepala-sekolah') }}">Baca sambutan lengkap <span aria-hidden="true">→</span></x-ui.button></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Program Keahlian --}}
    <section class="relative py-16 lg:py-20">
        <x-motif-geometric class="absolute right-0 top-10 size-40 text-primary-600/10 dark:text-tech-500/10" />
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <x-section-heading subtitle="Program Keahlian" title="Pilih Kompetensi Sesuai Bakatmu">
                    Jelajahi program yang tersedia dan temukan ruang belajar yang sesuai dengan minatmu.
                </x-section-heading>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:hidden" data-stagger>
                @forelse ($programs as $program)
                    <div data-stagger-item>
                        <x-program-card :program="$program" :tone="['emerald', 'orange', 'gold', 'navy'][$loop->index % 4]" />
                    </div>
                @empty
                    <div class="sm:col-span-2">
                        <x-ui.empty-state title="Belum ada program keahlian" />
                    </div>
                @endforelse
            </div>
            @if($programs->isNotEmpty())
            <div class="mt-12 hidden gap-8 lg:grid lg:grid-cols-2" data-program-index>
                <div class="flex flex-col gap-2" role="tablist" aria-label="Daftar program keahlian">
                    @foreach ($programs as $program)
                        <button
                            type="button"
                            role="tab"
                            id="program-tab-{{ $program->id }}"
                            aria-controls="program-pane-{{ $program->id }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}"
                            data-program-tab="{{ $program->id }}"
                            class="group flex items-center gap-5 rounded-2xl border border-transparent p-5 text-left transition-all duration-200 hover:border-slate-200 hover:bg-white hover:shadow-card data-[active=true]:border-slate-200 data-[active=true]:bg-white data-[active=true]:shadow-card dark:hover:border-slate-800 dark:hover:bg-slate-900 dark:data-[active=true]:border-slate-800 dark:data-[active=true]:bg-slate-900"
                            data-active="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            <span class="font-display text-sm font-extrabold text-slate-300 transition-colors group-hover:text-primary-500 dark:text-slate-600" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0">
                                <span class="block truncate font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $program->name }}</span>
                                <span class="mt-1 block truncate text-sm text-slate-500 dark:text-slate-400">{{ $program->short_description }}</span>
                            </span>
                            <svg class="ml-auto size-5 shrink-0 text-slate-300 transition-all group-hover:translate-x-1 group-hover:text-primary-500 dark:text-slate-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endforeach
                </div>
                <div class="relative">
                    <div class="sticky top-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900" data-program-panel>
                        @foreach ($programs as $program)
                            <div id="program-pane-{{ $program->id }}" data-program-pane="{{ $program->id }}" class="{{ $loop->first ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="program-tab-{{ $program->id }}">
                                <x-thumb :src="$program->image" ratio="aspect-[16/10]" :alt="$program->name" />
                                <div class="p-6">
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-energy-600 dark:text-energy-500">Program {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                    <h3 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $program->name }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $program->short_description }}</p>
                                    <x-ui.button size="sm" href="{{ route('programs.show', $program) }}" class="btn-arrow mt-5">
                                        Pelajari Program
                                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                        </svg>
                                    </x-ui.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @push('scripts')
            <script>
            (function () {
                const root = document.querySelector('[data-program-index]');
                if (!root) return;
                const tabs = Array.from(root.querySelectorAll('[data-program-tab]'));
                const panes = Array.from(root.querySelectorAll('[data-program-pane]'));
                function activate(id, focusPane) {
                    tabs.forEach((t) => {
                        const on = t.getAttribute('data-program-tab') === id;
                        t.setAttribute('data-active', on ? 'true' : 'false');
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                        t.setAttribute('tabindex', on ? '0' : '-1');
                    });
                    panes.forEach((p) => p.classList.toggle('hidden', p.getAttribute('data-program-pane') !== id));
                    if (focusPane === true) {
                        const pane = panes.find((p) => p.getAttribute('data-program-pane') === id);
                        if (pane && window.matchMedia('(prefers-reduced-motion: reduce)').matches === false) {
                            pane.animate(
                                [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }],
                                { duration: 320, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                            );
                        }
                    }
                }
                tabs.forEach((t) => {
                    const id = t.getAttribute('data-program-tab');
                    t.addEventListener('pointerenter', () => activate(id, true));
                    t.addEventListener('focus', () => activate(id, false));
                    t.addEventListener('click', () => activate(id, true));
                    t.addEventListener('keydown', (event) => {
                        const index = tabs.indexOf(t);
                        let target = null;
                        if (event.key === 'ArrowDown' || event.key === 'ArrowRight') target = tabs[(index + 1) % tabs.length];
                        if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') target = tabs[(index - 1 + tabs.length) % tabs.length];
                        if (event.key === 'Home') target = tabs[0];
                        if (event.key === 'End') target = tabs[tabs.length - 1];
                        if (!target) return;
                        event.preventDefault();
                        target.focus();
                        activate(target.getAttribute('data-program-tab'), true);
                    });
                });
            })();
            </script>
            @endpush
            @endif
        </div>
    </section>

    {{-- Dari ruang belajar menjadi karya — signature story --}}
    <section class="relative overflow-hidden bg-navy-900 py-16 lg:py-24" aria-labelledby="build-story-title">
        <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-24 top-1/3 size-72 rounded-full bg-tech-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 size-72 rounded-full bg-gold-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl" data-stagger>
                <p data-stagger-item class="text-xs font-bold uppercase tracking-[0.25em] text-tech-400">Pelajari • Praktikkan • Tumbuhkan</p>
                <h2 data-stagger-item id="build-story-title" class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Dari Ruang Belajar,<br />Menjadi Karya.</h2>
                <p data-stagger-item class="mt-4 max-w-xl leading-relaxed text-slate-300">Setiap program membuka kesempatan untuk memahami dasar, berlatih, dan menunjukkan perkembangan melalui pengalaman belajar yang terarah.</p>
            </div>
            <ol class="relative mt-12 grid gap-6 lg:grid-cols-3" data-journey="x">
                <span data-journey-fill class="absolute left-0 right-0 top-5 hidden h-0.5 origin-left rounded-full bg-gradient-to-r from-tech-400 via-gold-400 to-energy-500 lg:block" style="transform: scaleX(0); transform-origin: 0 50%;" aria-hidden="true"></span>
                @foreach ([
                    ['no' => '01', 'title' => 'Pahami Dasarnya', 'desc' => 'Mulai dari fondasi sesuai bidang dan program keahlian yang dipilih.'],
                    ['no' => '02', 'title' => 'Praktikkan', 'desc' => 'Ubah pemahaman menjadi latihan, eksplorasi, dan pengalaman belajar.'],
                    ['no' => '03', 'title' => 'Tunjukkan Perkembangan', 'desc' => 'Dokumentasikan proses dan hasil sebagai bagian dari perjalanan belajar.'],
                ] as $stage)
                    <li data-journey-step class="relative rounded-2xl border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm">
                        <span class="journey-dot flex size-10 items-center justify-center rounded-full border border-white/15 bg-white/5 font-display text-sm font-extrabold text-tech-300" aria-hidden="true">{{ $stage['no'] }}</span>
                        <h3 class="mt-4 font-display text-lg font-bold text-white">{{ $stage['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ $stage['desc'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Budaya sekolah --}}
    <section class="wash-offwhite py-16 lg:py-20" aria-label="Budaya sekolah">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl" data-stagger>
                <p data-stagger-item class="text-xs font-bold uppercase tracking-[0.25em] text-energy-600 dark:text-energy-500">Budaya Kami</p>
                <h2 data-stagger-item class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Belajar Sambil Membangun.</h2>
            </div>
            <ol class="relative mt-10 grid gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4" data-journey="x">
                <span data-journey-fill class="absolute left-0 right-0 top-5 hidden h-0.5 origin-left rounded-full bg-gradient-to-r from-primary-500 via-gold-500 to-energy-500 lg:block" style="transform: scaleX(0); transform-origin: 0 50%;" aria-hidden="true"></span>
                @foreach ([
                    ['no' => '01', 'title' => 'Belajar', 'desc' => 'Memahami dasar dengan benar.'],
                    ['no' => '02', 'title' => 'Berkolaborasi', 'desc' => 'Mengerjakan proyek bersama tim.'],
                    ['no' => '03', 'title' => 'Membangun', 'desc' => 'Mengubah ide menjadi karya.'],
                    ['no' => '04', 'title' => 'Berkarakter', 'desc' => 'Tumbuh dengan akhlak mulia.'],
                ] as $value)
                    <li data-journey-step class="relative">
                        <span class="journey-dot journey-ghost font-display text-4xl font-extrabold tracking-tight text-slate-400 dark:text-slate-500" aria-hidden="true">{{ $value['no'] }}</span>
                        <h3 class="mt-2 font-display text-lg font-bold uppercase tracking-wide text-slate-900 dark:text-white">{{ $value['title'] }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $value['desc'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Berita Terbaru --}}
    <section class="bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <x-section-heading subtitle="Kabar Sekolah" title="Berita Terbaru">
                    Ikuti perkembangan dan aktivitas terbaru di SMK Tahfizh Al-Fatih.
                </x-section-heading>
            </div>

            @if ($news->isNotEmpty())
            @php $headline = $news->first(); @endphp
            <a href="{{ route('news.show', $headline) }}" class="group mt-12 grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2" data-reveal="scale">
                <span class="block min-h-60 overflow-hidden">
                    <x-thumb :src="$headline->thumbnail" ratio="aspect-video md:aspect-auto md:h-full" :alt="$headline->title" class="h-full transition-transform duration-500 group-hover:scale-[1.03]" />
                </span>
                <span class="flex flex-col justify-center p-6 sm:p-10">
                    <span class="flex items-center gap-3 text-xs font-semibold uppercase tracking-wider">
                        <span class="text-gold-600 dark:text-gold-400">Sorotan</span>
                        <span class="text-slate-400">{{ $headline->published_at?->format('d M Y') }}</span>
                    </span>
                    <span class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400 sm:text-3xl">{{ $headline->title }}</span>
                    <span class="mt-3 line-clamp-3 leading-relaxed text-slate-500 dark:text-slate-400">{{ $headline->excerpt }}</span>
                    <span class="link-underline mt-4 w-fit text-sm font-bold text-primary-700 dark:text-primary-400">Baca Selengkapnya →</span>
                </span>
            </a>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-2" data-stagger>
                @foreach ($news->skip(1) as $item)
                    <a href="{{ route('news.show', $item) }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-card transition-colors hover:border-primary-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800" data-stagger-item>
                        <span class="block w-28 shrink-0 overflow-hidden rounded-xl">
                            <x-thumb :src="$item->thumbnail" ratio="aspect-[4/3]" :alt="$item->title" class="transition-transform duration-300 group-hover:scale-105" />
                        </span>
                        <span class="min-w-0 flex-1 py-1">
                            <span class="text-xs font-medium uppercase tracking-wider text-gold-600 dark:text-gold-400">{{ $item->published_at?->format('d M Y') }}</span>
                            <span class="mt-1 line-clamp-2 block font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $item->title }}</span>
                        </span>
                        <svg class="mr-1 size-5 shrink-0 text-slate-300 transition-all group-hover:translate-x-1 group-hover:text-primary-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endforeach
                @if ($news->isEmpty())
                    <div class="md:col-span-2">
                        <x-ui.empty-state title="Belum ada berita" />
                    </div>
                @endif
            </div>

            @if ($news->isNotEmpty())
                <div class="mt-10 text-center">
                    <x-ui.button variant="outline" href="{{ route('news.index') }}">Lihat Semua Berita</x-ui.button>
                </div>
            @endif
        </div>
    </section>

    {{-- Pengumuman --}}
    <section class="py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <x-section-heading subtitle="Info Resmi" title="Pengumuman">
                    Informasi resmi seputar kegiatan dan agenda sekolah.
                </x-section-heading>
            </div>

            <div class="mx-auto mt-12 max-w-3xl space-y-4" data-stagger>
                @forelse ($announcements as $announcement)
                    <div data-stagger-item class="flex items-start gap-4 rounded-xl border border-slate-200 border-l-4 border-l-gold-500 bg-white p-5 shadow-card dark:border-slate-800 dark:border-l-gold-500 dark:bg-slate-900">
                        <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-50 text-accent-600 dark:bg-accent-950 dark:text-accent-400" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.25 4.5a2.25 2.25 0 012.25-2.25h9a2.25 2.25 0 012.25 2.25v13.5a.75.75 0 01-.75.75H6a.75.75 0 01-.75-.75V4.5zM6 18.75h12v2.25a.75.75 0 01-.75.75h-9a.75.75 0 01-.75-.75v-2.25z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $announcement->title }}</h3>
                            <div class="prose-content mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{!! $announcement->content !!}</div>
                            <p class="mt-2 text-xs font-medium uppercase tracking-wider text-gold-600 dark:text-gold-400">{{ $announcement->published_at?->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="Belum ada pengumuman" />
                @endforelse
            </div>
        </div>
    </section>

    {{-- Galeri --}}
    <section class="bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <x-section-heading subtitle="Dokumentasi" title="Galeri Sekolah">
                    Momen dan kegiatan terbaik dari lingkungan SMK Tahfizh Al-Fatih.
                </x-section-heading>
            </div>

            <div class="reveal mt-12">
                <x-mosaic-gallery :galleries="$galleries" />
            </div>

            @if ($galleries->isNotEmpty())
                <div class="mt-10 text-center">
                    <x-ui.button variant="outline" href="{{ route('gallery.index') }}">Lihat Galeri Lengkap</x-ui.button>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Pendaftaran — state story --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-navy-900 shadow-soft" data-reveal="scale" data-spotlight>
            <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-energy-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-16 size-48 rounded-full bg-gold-500/20 blur-3xl" aria-hidden="true"></div>

            <div class="relative grid gap-10 p-6 sm:p-10 lg:grid-cols-5 lg:py-12">
                <div class="text-center lg:col-span-3 lg:text-left" data-mask-group>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.2em] text-gold-400 ring-1 ring-inset ring-white/20">
                        <span class="relative flex size-2" aria-hidden="true">
                            <span class="absolute inline-flex size-full animate-ping rounded-full {{ $ppdbState->status === 'open' ? 'bg-emerald-400' : 'bg-slate-400' }} opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full {{ $ppdbState->status === 'open' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        </span>
                        PPDB {{ $ppdbState->period?->academic_year ?? '' }} — {{ $ppdbState->publicLabel() }}
                    </p>
                    <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        <span data-mask-line><span>
                        @if($ppdbState->status === 'open') Pendaftaran Dibuka.
                        @elseif($ppdbState->status === 'completed') Telah Selesai.
                        @elseif($ppdbState->status === 'full') Kuota Terpenuhi.
                        @elseif($ppdbState->status === 'upcoming') Segera Dibuka.
                        @elseif($ppdbState->status === 'closed') Telah Ditutup.
                        @else Informasi PPDB. @endif
                        </span></span>
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-base lg:mx-0">
                        @if($ppdbState->status === 'open')
                            Pendaftaran Peserta Didik Baru sedang dibuka.@if($ppdbState->quota) Kuota tersisa: {{ $ppdbState->remainingQuota() }} dari {{ $ppdbState->quota }}.@endif
                        @elseif($ppdbState->status === 'upcoming')
                            Pendaftaran PPDB belum dibuka. @if($ppdbState->period?->opens_at)Dibuka {{ $ppdbState->period->opens_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }} WIB.@endif
                        @elseif($ppdbState->status === 'full')
                            Kuota PPDB telah terpenuhi. Pendaftaran online tidak menerima calon siswa baru.
                        @elseif($ppdbState->status === 'closed')
                            Periode PPDB telah berakhir. Pendaftaran berikutnya belum dibuka kembali.
                        @elseif($ppdbState->status === 'completed')
                            Seluruh rangkaian PPDB periode ini telah selesai.
                        @else
                            Informasi pembukaan pendaftaran akan diumumkan melalui website resmi sekolah.
                        @endif
                    </p>
                    <x-digital-pulse :steps="['Daftar', 'Verifikasi', 'Diterima']" class="mx-auto mt-5 max-w-md text-tech-400 lg:mx-0" />
                    <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                        @if($ppdbState->canRegister())
                        <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm btn-arrow" shine data-magnetic>Daftar Sekarang
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </x-ui.button>
                        @elseif($ppdbState->portalEntryVisible())
                        <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm" data-magnetic>Informasi PPDB</x-ui.button>
                        @endif
                        @if($ppdbState->portalEntryVisible())
                        <x-ui.button variant="outline" size="lg" href="{{ route('portal.login') }}" class="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:border-white/40 focus-visible:outline-white" data-magnetic>Masuk Portal</x-ui.button>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col justify-center gap-3 lg:col-span-2">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Tahun Ajaran</p>
                        <p class="mt-1 font-display text-xl font-extrabold text-white">{{ $ppdbState->period?->academic_year ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Status</p>
                        <p class="mt-1 font-display text-xl font-extrabold text-gold-400">{{ $ppdbState->publicLabel() }}</p>
                    </div>
                    @if($ppdbState->quota !== null)
                    <div class="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Kuota Tersisa</p>
                        <p class="mt-1 font-display text-xl font-extrabold text-white">{{ $ppdbState->remainingQuota() }} <span class="text-sm font-semibold text-slate-400">dari {{ $ppdbState->quota }}</span></p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Kontak CTA --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8 lg:pb-20">
        <div class="flex flex-col items-center justify-between gap-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:p-8" data-reveal>
            <div class="text-center sm:text-left">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-primary-600 dark:text-primary-400">Hubungi Kami</p>
                <h2 class="mt-2 font-display text-xl font-extrabold text-slate-900 dark:text-white sm:text-2xl">Ada pertanyaan? Kami siap membantu.</h2>
                @php($homeContacts = array_values(array_filter([\App\Models\SiteSetting::get('school_phone'), \App\Models\SiteSetting::get('school_email')])))
                @if($homeContacts !== [])<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ implode(' • ', $homeContacts) }}</p>@endif
            </div>
            <div class="flex shrink-0 gap-3">
                <x-ui.button href="{{ route('contact.index') }}" class="btn-arrow" data-magnetic>Kirim Pesan
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </x-ui.button>
                <x-ui.button variant="outline" href="{{ route('pages.show', 'profil') }}">Profil Sekolah</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.app>
