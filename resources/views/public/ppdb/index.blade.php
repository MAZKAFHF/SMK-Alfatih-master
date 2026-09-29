<x-layouts.app :title="'PPDB Online'">
    <x-page-hero
        variant="tech"
        eyebrow="Penerimaan Peserta Didik Baru"
        title="PPDB SMK Tahfizh Al-Fatih."
        :description="$ppdbState->canRegister()
            ? 'Pelajari informasi periode, siapkan dokumen, dan ikuti proses pendaftaran melalui portal resmi.'
            : 'Lihat status periode, jadwal, persyaratan awal, dan alur resmi penerimaan peserta didik baru.'"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'PPDB'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            {{-- Panel ajakan pendaftaran --}}
            <div class="reveal relative overflow-hidden rounded-2xl bg-navy-900 px-6 py-14 text-center shadow-card sm:px-12">
                <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-energy-500/25 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 size-64 rounded-full bg-gold-500/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative" data-reveal="fade">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-white ring-1 ring-inset ring-white/25">
                        <span class="size-1.5 rounded-full bg-emerald-300" aria-hidden="true"></span>
                        PPDB Tahun Ajaran {{ $ppdbState->period?->academic_year ?? '—' }} &mdash; {{ $ppdbState->publicLabel() }}
                    </span>

                    @if($ppdbState->status === 'open')
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        Pendaftaran PPDB Sedang Dibuka
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Daftarkan dirimu di SMK Tahfizh Al-Fatih. Isi formulir online, pilih program keahlian, lalu pantau seleksi dari portal.
                    </p>
                    @elseif($ppdbState->status === 'upcoming')
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        PPDB Belum Dibuka
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Penerimaan Peserta Didik Baru SMK Tahfizh Al-Fatih belum dibuka saat ini. Silakan kembali pada jadwal pembukaan di bawah. Informasi terbaru juga tersedia melalui website resmi sekolah.
                    </p>
                    @elseif($ppdbState->status === 'full')
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        Kuota PPDB Telah Terpenuhi
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Pendaftaran online untuk periode ini tidak lagi menerima calon siswa baru karena kuota telah terpenuhi.
                    </p>
                    @elseif($ppdbState->status === 'completed')
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        PPDB {{ $ppdbState->period?->academic_year ?? '' }} Telah Selesai
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Seluruh rangkaian PPDB periode ini telah selesai.
                    </p>
                    @elseif($ppdbState->status === 'closed')
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        PPDB Telah Ditutup
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Terima kasih atas antusiasme Anda. Periode PPDB telah berakhir dan pendaftaran periode berikutnya belum dibuka kembali. Informasi jadwal berikutnya akan diumumkan melalui website resmi sekolah.
                    </p>
                    @else
                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        Informasi PPDB
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        PPDB saat ini belum tersedia. Informasi pembukaan pendaftaran akan diumumkan melalui website resmi SMK Tahfizh Al-Fatih.
                    </p>
                    @endif

                    {{-- Jadwal + kuota kanonis --}}
                    @if($ppdbState->period && ($ppdbState->period->opens_at || $ppdbState->period->closes_at || $ppdbState->quota))
                    <dl class="mx-auto mt-6 grid max-w-2xl gap-3 text-left sm:grid-cols-3">
                        <div class="rounded-lg bg-white/10 px-4 py-3 ring-1 ring-inset ring-white/20">
                            <dt class="text-[11px] font-bold uppercase tracking-widest text-primary-100">Mulai</dt>
                            <dd class="mt-1 text-sm font-bold text-white">{{ $ppdbState->period->opens_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }}</dd>
                            <dd class="text-xs text-primary-100">{{ $ppdbState->period->opens_at?->timezone('Asia/Jakarta')->format('H.i') }} WIB</dd>
                        </div>
                        <div class="rounded-lg bg-white/10 px-4 py-3 ring-1 ring-inset ring-white/20">
                            <dt class="text-[11px] font-bold uppercase tracking-widest text-primary-100">Selesai</dt>
                            <dd class="mt-1 text-sm font-bold text-white">{{ $ppdbState->period->closes_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }}</dd>
                            <dd class="text-xs text-primary-100">{{ $ppdbState->period->closes_at?->timezone('Asia/Jakarta')->format('H.i') }} WIB</dd>
                        </div>
                        <div class="rounded-lg bg-white/10 px-4 py-3 ring-1 ring-inset ring-white/20">
                            <dt class="text-[11px] font-bold uppercase tracking-widest text-primary-100">Status</dt>
                            <dd class="mt-1 text-sm font-bold text-white">{{ $ppdbState->publicLabel() }}</dd>
                            <dd class="text-xs text-primary-100">@if($ppdbState->quota !== null)Kuota tersisa: {{ $ppdbState->remainingQuota() }} dari {{ $ppdbState->quota }}@else Tanpa batas kuota @endif</dd>
                        </div>
                    </dl>
                    @endif

                    @if($ppdbState->portalEntryVisible())
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        @if($ppdbState->canRegister())
                        <a data-magnetic href="{{ route('portal.register') }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-white px-6 py-3 text-base font-semibold text-primary-800 shadow-sm transition-colors duration-150 select-none whitespace-nowrap hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:w-auto">
                            Buat Akun & Daftar
                        </a>
                        @else
                        <a href="{{ route('portal.register') }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-white/10 px-6 py-3 text-base font-semibold text-white ring-1 ring-inset ring-white/30 transition-colors hover:bg-white/20 sm:w-auto">
                            Buat Akun Portal
                        </a>
                        @endif
                        <a href="{{ route('portal.login') }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-energy-500 px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-energy-600 sm:w-auto">
                            Masuk Portal
                        </a>
                    </div>
                    <p class="mt-4 text-xs text-primary-100">Satu akun orang tua dapat digunakan untuk banyak anak dan tetap dapat dibuat kapan saja. Pendaftaran, pemantauan proses, riwayat periode, wawancara, dan hasil tersedia secara privat di Portal Pendaftar.</p>
                    @endif
                </div>
            </div>

            {{-- Alur pendaftaran 6 langkah — journey rail --}}
            <div class="mt-12" data-journey>
                <h3 class="text-center text-lg font-bold tracking-tight text-slate-900 dark:text-white">Alur Pendaftaran</h3>
                <p class="mx-auto mt-2 max-w-xl text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400">Enam langkah jelas — Anda selalu tahu posisi dan aksi berikutnya.</p>
                <ol class="relative mx-auto mt-8 max-w-2xl space-y-0">
                    <span data-journey-fill class="absolute bottom-6 left-[19px] top-2 w-0.5 rounded-full bg-gradient-to-b from-primary-500 via-gold-500 to-energy-500" aria-hidden="true"></span>
                    @foreach ([
                        ['title' => 'Buat Akun', 'desc' => 'Daftar dengan email + password. Satu akun untuk semua anak.'],
                        ['title' => 'Isi Pendaftaran', 'desc' => 'Data siswa, alamat, orang tua, sekolah, satu program, dokumen.'],
                        ['title' => 'Verifikasi Dokumen', 'desc' => 'Panitia memeriksa per-dokumen; perbaiki jika diminta tanpa daftar baru.'],
                        ['title' => 'Pilih Jadwal Wawancara', 'desc' => 'Pilih slot tersedia; aman dari double booking.'],
                        ['title' => 'Wawancara & Tahfizh/Tahsin', 'desc' => 'Hadir sesuai jadwal dengan instruksi lokasi.'],
                        ['title' => 'Lihat Hasil', 'desc' => 'Panel resmi Lulus / Belum Lulus + lanjut via WhatsApp admin.'],
                    ] as $step)
                        <li data-journey-step class="relative flex gap-4 pb-6 last:pb-0">
                            <span class="journey-dot relative z-10 mt-1 flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-primary-200 bg-white text-sm font-extrabold text-primary-700 dark:border-primary-800 dark:bg-slate-900 dark:text-primary-400">{{ $loop->iteration }}</span>
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</h4>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-12 grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border bg-white p-6 dark:bg-slate-900"><h3 class="font-bold">Dokumen Awal</h3><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-600"><li>Kartu Keluarga (KK)</li><li>KTP Orang Tua / Wali</li><li>Akta Kelahiran</li><li>Rapor</li><li>Foto Siswa</li></ul><p class="mt-2 text-xs text-slate-400">Ijazah dapat diminta pada tahap lanjut. Persyaratan final mengikuti periode aktif.</p></div>
                <div class="rounded-2xl border bg-white p-6 dark:bg-slate-900"><h3 class="font-bold">FAQ</h3><ul class="mt-2 space-y-2 text-sm text-slate-600"><li><strong>Bisakah 1 akun untuk banyak anak?</strong> Ya — tambah calon siswa di portal.</li><li><strong>Dokumen salah?</strong> Perbaiki via portal tanpa daftar baru.</li><li><strong>Ubah jadwal?</strong> Ajukan reschedule beralasan; jadwal lama aman.</li><li><strong>Kapan ijazah?</strong> Tahap lanjut via info admin.</li><li><strong>Hasil?</strong> Panel resmi di portal + email; lanjut via WhatsApp.</li></ul></div>
            </div>
        </div>
    </section>
</x-layouts.app>
