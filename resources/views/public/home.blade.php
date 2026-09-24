<x-layouts.app>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-white to-white dark:from-primary-950/50 dark:via-slate-950 dark:to-slate-950">
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-primary-100 blur-3xl dark:bg-primary-900/50" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-24 bottom-0 size-80 rounded-full bg-accent-100 blur-3xl dark:bg-accent-900/50" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-1.5 text-xs font-semibold text-primary-800 ring-1 ring-inset ring-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:ring-primary-700">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    PPDB 2026/2027 Telah Dibuka
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                    SMK Tahfizh <span class="text-primary-600 dark:text-primary-400">Al-Fatih</span>
                </h1>

                <p class="mt-5 text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                    Sekolah menengah kejuruan berbasis tahfizh Al-Qur'an. Mencetak generasi unggul, berakhlak mulia, dan siap bersaing di dunia kerja.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-ui.button size="lg" href="{{ route('ppdb.index') }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Daftar PPDB
                    </x-ui.button>
                    <x-ui.button size="lg" variant="outline" href="{{ route('pages.show', 'profil') }}">Lihat Profil Sekolah</x-ui.button>
                </div>

                <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-2">
                        <svg class="size-5 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                        <span>850+ Siswa</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="size-5 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span>4 Program Keahlian</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="size-5 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        <span>Berbasis Tahfizh</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA PPDB --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-primary-700 to-primary-900 px-6 py-8 shadow-soft sm:px-10 lg:py-10">
            <div class="flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-extrabold text-white sm:text-3xl">Bergabunglah Bersama Kami</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Pendaftaran Peserta Didik Baru tahun ajaran 2026/2027 telah dibuka. Kuota terbatas!
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}">Daftar Sekarang</x-ui.button>
                    <x-ui.button variant="outline" size="lg" href="{{ route('ppdb.status') }}" class="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:border-white/40 focus-visible:outline-white">Cek Status</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    {{-- Statistik — configurable via SiteSetting --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Program Keahlian', 'value' => $stats['programs'] ?? '4'],
                ['label' => 'Tahun Berdiri', 'value' => $stats['founded'] ?? '2016'],
                ['label' => 'Siswa Aktif', 'value' => $stats['students'] ?? '850+'],
                ['label' => 'Alumni Tersebar', 'value' => $stats['alumni'] ?? '1200+'],
            ] as $stat)
                @if(!blank($stat['value']))
                    <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <dd class="text-3xl font-extrabold text-primary-600 dark:text-primary-400">{{ $stat['value'] }}</dd>
                        <dt class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</dt>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>

    {{-- Tentang --}}
    <section class="bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <x-section-heading align="left" subtitle="Tentang Kami" title="Sekolah Vokasi Berbasis Tahfizh Al-Qur'an">
                    SMK Tahfizh Al-Fatih memadukan pendidikan kejuruan modern dengan pembinaan hafalan Al-Qur'an. Kami percaya lulusan terbaik adalah mereka yang tidak hanya unggul dalam kompetensi, tetapi juga kokoh dalam iman dan akhlak.
                </x-section-heading>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        ['title' => 'Kurikulum Vokasi Modern', 'desc' => 'Pembelajaran berbasis proyek dan relevan dengan kebutuhan industri.'],
                        ['title' => 'Program Tahfizh Terstruktur', 'desc' => 'Target hafalan jelas dengan bimbingan guru bersanad.'],
                        ['title' => 'Lingkungan Islami & Nyaman', 'desc' => 'Budaya sekolah yang islami, disiplin, dan menyenangkan.'],
                    ] as $feature)
                        <li class="flex items-start gap-3.5">
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

            <div class="grid grid-cols-2 gap-4">
                <x-thumb ratio="aspect-[3/4]" class="rounded-xl" alt="Kegiatan sekolah" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="mt-8 rounded-xl" alt="Fasilitas sekolah" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="-mt-8 rounded-xl" alt="Prestasi siswa" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="rounded-xl" alt="Pembelajaran" icon="camera" />
            </div>
        </div>
    </section>

    {{-- Program Keahlian --}}
    <section class="py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading subtitle="Program Keahlian" title="Pilih Kompetensi Sesuai Bakatmu">
                Empat program keahlian yang membekali siswa dengan keterampilan siap kerja di era digital.
            </x-section-heading>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($programs as $program)
                    <a href="{{ route('programs.show', $program) }}" class="group">
                        <x-ui.card padding="false" hover="true" class="h-full overflow-hidden">
                            <x-thumb :src="$program->image" ratio="aspect-[4/3]" alt="{{ $program->name }}" />
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $program->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $program->short_description }}</p>
                                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                                    Selengkapnya
                                    <svg class="size-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </x-ui.card>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-4">
                        <x-ui.empty-state title="Belum ada program keahlian" />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Berita Terbaru --}}
    <section class="bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading subtitle="Kabar Sekolah" title="Berita Terbaru">
                Ikuti perkembangan dan aktivitas terbaru di SMK Tahfizh Al-Fatih.
            </x-section-heading>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @forelse ($news as $item)
                    <a href="{{ route('news.show', $item) }}" class="group">
                        <x-ui.card padding="false" hover="true" class="h-full overflow-hidden">
                            <x-thumb :src="$item->thumbnail" ratio="aspect-video" alt="{{ $item->title }}" />
                            <div class="p-5">
                                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">{{ $item->published_at?->format('d M Y') }}</p>
                                <h3 class="mt-2 line-clamp-2 font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $item->title }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $item->excerpt }}</p>
                            </div>
                        </x-ui.card>
                    </a>
                @empty
                    <div class="md:col-span-3">
                        <x-ui.empty-state title="Belum ada berita" />
                    </div>
                @endforelse
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
            <x-section-heading subtitle="Info Resmi" title="Pengumuman">
                Informasi resmi seputar kegiatan dan agenda sekolah.
            </x-section-heading>

            <div class="mx-auto mt-12 max-w-3xl space-y-4">
                @forelse ($announcements as $announcement)
                    <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-50 text-accent-600 dark:bg-accent-950 dark:text-accent-400" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.25 4.5a2.25 2.25 0 012.25-2.25h9a2.25 2.25 0 012.25 2.25v13.5a.75.75 0 01-.75.75H6a.75.75 0 01-.75-.75V4.5zM6 18.75h12v2.25a.75.75 0 01-.75.75h-9a.75.75 0 01-.75-.75v-2.25z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $announcement->title }}</h3>
                            <div class="prose-content mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{!! $announcement->content !!}</div>
                            <p class="mt-2 text-xs font-medium text-primary-600 dark:text-primary-400">{{ $announcement->published_at?->format('d M Y') }}</p>
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
            <x-section-heading subtitle="Dokumentasi" title="Galeri Sekolah">
                Momen dan kegiatan terbaik dari lingkungan SMK Tahfizh Al-Fatih.
            </x-section-heading>

            <div class="mt-12 grid grid-cols-2 gap-4 md:grid-cols-3">
                @forelse ($galleries as $gallery)
                    <x-thumb :src="$gallery->image" ratio="aspect-[4/3]" class="rounded-xl" alt="{{ $gallery->title }}" icon="camera" />
                @empty
                    <div class="col-span-full">
                        <x-ui.empty-state title="Belum ada foto" />
                    </div>
                @endforelse
            </div>

            @if ($galleries->isNotEmpty())
                <div class="mt-10 text-center">
                    <x-ui.button variant="outline" href="{{ route('gallery.index') }}">Lihat Galeri Lengkap</x-ui.button>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Pendaftaran --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-700 to-primary-900 px-6 py-8 shadow-soft sm:px-10 lg:py-10">
            <div class="pointer-events-none absolute -left-16 -top-16 size-48 rounded-full bg-primary-500/30 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -right-16 size-48 rounded-full bg-accent-500/20 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-extrabold text-white sm:text-3xl">Bergabunglah Bersama Kami</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Pendaftaran Peserta Didik Baru tahun ajaran 2026/2027 telah dibuka. Kuota terbatas!
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}">Daftar Sekarang</x-ui.button>
                    <x-ui.button variant="outline" size="lg" href="{{ route('ppdb.status') }}" class="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:border-white/40 focus-visible:outline-white">Cek Status</x-ui.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
