<x-layouts.app>
    {{-- HERO — Living Campus --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-white to-white dark:from-night-900 dark:via-slate-950 dark:to-slate-950">
        <div class="tech-grid-light pointer-events-none absolute inset-0 dark:hidden" aria-hidden="true"></div>
        <div class="tech-grid pointer-events-none absolute inset-0 hidden dark:block" aria-hidden="true"></div>
        <x-motif-geometric class="absolute -left-10 top-16 size-44 text-primary-600/10 dark:text-tech-500/10" />
        <x-motif-geometric class="absolute -right-12 bottom-10 size-56 text-gold-500/15" />

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:py-24">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-1.5 text-xs font-semibold text-primary-800 ring-1 ring-inset ring-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:ring-primary-700">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    PPDB {{ $ppdbState->period?->academic_year ?? '' }} {{ $ppdbState->publicLabel() }}
                </div>

                <p class="mt-6 text-xs font-bold uppercase tracking-[0.25em] text-energy-600 dark:text-energy-500">SMK Tahfizh Al-Fatih</p>
                <h1 class="mt-3 font-display text-4xl font-extrabold leading-[1.05] tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                    Membangun Generasi
                    <br />
                    <span class="text-primary-600 dark:text-tech-400">Teknologi.</span>
                    <span class="word-swap text-gold-600 dark:text-gold-400" data-word-swap data-words='["Karakter.", "Kreativitas.", "Masa Depan."]'><span>Karakter.</span></span>
                </h1>

                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                    Sekolah menengah kejuruan berbasis tahfizh Al-Qur'an. Mencetak generasi unggul, berakhlak mulia, dan siap bersaing di dunia kerja.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @if($ppdbState->canRegister())
                    <x-ui.button size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm" shine>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        Daftar PPDB
                    </x-ui.button>
                    @else
                    <x-ui.button size="lg" variant="outline" href="{{ route('ppdb.index') }}" class="clip-corner-sm">Lihat Informasi PPDB</x-ui.button>
                    @endif
                    <x-ui.button size="lg" variant="outline" href="{{ route('pages.show', 'profil') }}">Lihat Profil Sekolah</x-ui.button>
                </div>

                <x-digital-pulse :steps="['Learn', 'Build', 'Impact']" class="mt-10 max-w-md text-primary-700 dark:text-tech-400" />
            </div>

            <div class="reveal relative" style="--reveal-delay: 120ms">
                <x-rpl-panel />
                <div class="absolute -bottom-5 -left-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-soft backdrop-blur dark:border-slate-700 dark:bg-slate-900/95 sm:-left-6">
                    <span class="flex size-10 items-center justify-center rounded-lg bg-gold-500/15 text-gold-600 dark:text-gold-400" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-extrabold text-slate-900 dark:text-white">Tahfizh Terstruktur</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">Bimbingan guru bersanad</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="relative border-t border-slate-200/70 bg-white/70 py-3 backdrop-blur dark:border-slate-800/70 dark:bg-slate-950/70">
            <x-marquee-strip :items="['Rekayasa Perangkat Lunak', 'Project-Based Learning', 'Tahfizh & Karakter', 'Future Skills']" class="text-slate-600 dark:text-slate-300" />
        </div>
    </section>

    {{-- Statistik --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <x-stat-ribbon :stats="[
            ['label' => 'Program Keahlian', 'value' => $stats['programs'] ?? '4'],
            ['label' => 'Tahun Berdiri', 'value' => $stats['founded'] ?? '2016'],
            ['label' => 'Siswa Aktif', 'value' => $stats['students'] ?? '850+'],
            ['label' => 'Alumni Tersebar', 'value' => $stats['alumni'] ?? '1200+'],
        ]" />
    </section>

    {{-- Tentang --}}
    <section class="bg-white py-16 dark:bg-slate-950 lg:py-20">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="reveal">
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

            <div class="reveal grid grid-cols-2 gap-4" style="--reveal-delay: 100ms">
                <x-thumb ratio="aspect-[3/4]" class="rounded-xl" alt="Kegiatan sekolah" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="mt-8 rounded-xl" alt="Fasilitas sekolah" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="-mt-8 rounded-xl" alt="Prestasi siswa" icon="camera" />
                <x-thumb ratio="aspect-[3/4]" class="rounded-xl" alt="Pembelajaran" icon="camera" />
            </div>
        </div>
    </section>

    {{-- Program Keahlian --}}
    <section class="relative py-16 lg:py-20">
        <x-motif-geometric class="absolute right-0 top-10 size-40 text-primary-600/10 dark:text-tech-500/10" />
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal">
                <x-section-heading subtitle="Program Keahlian" title="Pilih Kompetensi Sesuai Bakatmu">
                    Empat program keahlian yang membekali siswa dengan keterampilan siap kerja di era digital.
                </x-section-heading>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($programs as $program)
                    <div class="reveal" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                        <x-program-card :program="$program" :tone="['emerald', 'orange', 'gold', 'navy'][$loop->index % 4]" />
                    </div>
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
            <div class="reveal">
                <x-section-heading subtitle="Kabar Sekolah" title="Berita Terbaru">
                    Ikuti perkembangan dan aktivitas terbaru di SMK Tahfizh Al-Fatih.
                </x-section-heading>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @forelse ($news as $item)
                    <a href="{{ route('news.show', $item) }}" class="group reveal" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                        <x-ui.card padding="false" hover="true" class="lift h-full overflow-hidden">
                            <x-thumb :src="$item->thumbnail" ratio="aspect-video" alt="{{ $item->title }}" />
                            <div class="p-5">
                                <p class="text-xs font-medium uppercase tracking-wider text-gold-600 dark:text-gold-400">{{ $item->published_at?->format('d M Y') }}</p>
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
            <div class="reveal">
                <x-section-heading subtitle="Info Resmi" title="Pengumuman">
                    Informasi resmi seputar kegiatan dan agenda sekolah.
                </x-section-heading>
            </div>

            <div class="mx-auto mt-12 max-w-3xl space-y-4">
                @forelse ($announcements as $announcement)
                    <div class="reveal flex items-start gap-4 rounded-xl border border-slate-200 border-l-4 border-l-gold-500 bg-white p-5 shadow-card dark:border-slate-800 dark:border-l-gold-500 dark:bg-slate-900">
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

    {{-- CTA Pendaftaran --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-2xl bg-navy-900 px-6 py-8 shadow-soft sm:px-10 lg:py-10">
            <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-energy-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-16 size-48 rounded-full bg-gold-500/20 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold-400">PPDB {{ $ppdbState->period?->academic_year ?? '' }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">Your Next Chapter Starts Here.</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-base">
                        @if($ppdbState->status === 'open')
                            Pendaftaran Peserta Didik Baru sedang dibuka.@if($ppdbState->quota) Kuota tersisa: {{ $ppdbState->remainingQuota() }} dari {{ $ppdbState->quota }}.@endif
                        @elseif($ppdbState->status === 'upcoming')
                            Pendaftaran PPDB belum dibuka. @if($ppdbState->period?->opens_at)Dibuka {{ $ppdbState->period->opens_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') }} WIB.@endif
                        @elseif($ppdbState->status === 'full')
                            Kuota PPDB telah terpenuhi. Pendaftaran online tidak menerima calon siswa baru.
                        @elseif($ppdbState->status === 'closed')
                            Periode PPDB telah berakhir. Pendaftaran berikutnya belum dibuka kembali.
                        @else
                            Informasi pembukaan pendaftaran akan diumumkan melalui website resmi sekolah.
                        @endif
                    </p>
                    <x-digital-pulse :steps="['Daftar', 'Verifikasi', 'Diterima']" class="mx-auto mt-5 max-w-md text-tech-400 lg:mx-0" />
                </div>
                <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                    @if($ppdbState->canRegister())
                    <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm">Daftar Sekarang</x-ui.button>
                    @else
                    <x-ui.button variant="accent" size="lg" href="{{ route('ppdb.index') }}" class="clip-corner-sm">Informasi PPDB</x-ui.button>
                    @endif
                    <x-ui.button variant="outline" size="lg" href="{{ route('portal.login') }}" class="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:border-white/40 focus-visible:outline-white">Masuk Portal</x-ui.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
