<x-layouts.app :title="'PPDB Online'">
    <x-page-header
        title="PPDB Online"
        subtitle="Penerimaan Peserta Didik Baru SMK Tahfizh Al-Fatih Tahun Ajaran 2026/2027"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'PPDB'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            {{-- Panel ajakan pendaftaran --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-700 via-primary-800 to-primary-950 px-6 py-14 text-center shadow-card sm:px-12">
                <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 size-64 rounded-full bg-accent-500/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-white ring-1 ring-inset ring-white/25">
                        <span class="size-1.5 rounded-full bg-emerald-300" aria-hidden="true"></span>
                        PPDB Tahun Ajaran 2026/2027 &mdash; Dibuka
                    </span>

                    <h2 class="mx-auto mt-5 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        Daftarkan Dirimu di SMK Tahfizh Al-Fatih
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-primary-100 sm:text-base">
                        Isi formulir secara online, pilih program keahlian yang diminati, lalu pantau proses seleksi langsung dari halaman cek status.
                    </p>

                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="{{ route('ppdb.siswa') }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-white px-6 py-3 text-base font-semibold text-primary-800 shadow-sm transition-colors duration-150 select-none whitespace-nowrap hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:w-auto">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Daftar Sekarang
                        </a>
                        <a href="{{ route('ppdb.status') }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg px-6 py-3 text-base font-semibold text-white ring-1 ring-inset ring-white/30 transition-colors duration-150 select-none whitespace-nowrap hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:w-auto">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            Cek Status Pendaftaran
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alur pendaftaran --}}
            <div class="mt-12">
                <h3 class="text-center text-lg font-bold tracking-tight text-slate-900 dark:text-white">Alur Pendaftaran</h3>
                <p class="mx-auto mt-2 max-w-xl text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Hanya tiga langkah mudah untuk bergabung bersama kami.
                </p>

                <ol class="mt-8 grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        ['title' => 'Lengkapi Formulir', 'desc' => 'Isi data diri, asal sekolah, dan pilih program keahlian yang diminati.'],
                        ['title' => 'Verifikasi Panitia', 'desc' => 'Panitia akan memverifikasi kelengkapan dan kebenaran data Anda.'],
                        ['title' => 'Pengumuman Hasil', 'desc' => 'Pantau hasil seleksi kapan saja melalui halaman cek status.'],
                    ] as $i => $step)
                        <li class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                            <span class="flex size-10 items-center justify-center rounded-full bg-primary-100 text-sm font-extrabold text-primary-700 dark:bg-primary-900 dark:text-primary-400">
                                {{ $i + 1 }}
                            </span>
                            <h4 class="mt-4 text-base font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</h4>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
</x-layouts.app>
