<footer class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="40" height="40" class="size-10 rounded-xl bg-white/10 object-contain p-1 dark:bg-white/5" />
                    <span class="leading-tight">
                        <span class="block text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">SMK TAHFIZH</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-primary-600 dark:text-primary-400">Al-Fatih</span>
                    </span>
                </a>
                <p class="mt-4 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Sekolah menengah kejuruan berbasis tahfizh Al-Qur'an yang mencetak generasi berprestasi, berakhlak mulia, dan siap kerja.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-900 dark:text-white">Tautan Cepat</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ([
                        ['Beranda', route('home')],
                        ['Program Keahlian', route('programs.index')],
                        ['Berita', route('news.index')],
                        ['Galeri', route('gallery.index')],
                        ['Kontak', route('contact.index')],
                    ] as [$label, $url])
                        <li>
                            <a href="{{ $url }}" class="text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-900 dark:text-white">PPDB</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('ppdb.siswa') }}" class="text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400">Daftar PPDB</a></li>
                    <li><a href="{{ route('ppdb.status') }}" class="text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400">Cek Status</a></li>
                    <li><a href="{{ route('announcements.index') }}" class="text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400">Pengumuman</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-900 dark:text-white">Kontak</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>{{ \App\Models\SiteSetting::get('school_address', 'Jl. Pendidikan No. 1, Jakarta') }}</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        <span>{{ \App\Models\SiteSetting::get('school_phone', '(021) 1234-5678') }}</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span>{{ \App\Models\SiteSetting::get('school_email', 'info@smkalfatih.sch.id') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 border-t border-slate-200 pt-6 text-center text-xs text-slate-400 dark:border-slate-800">
            &copy; {{ date('Y') }} SMK Tahfizh Al-Fatih. Seluruh hak cipta dilindungi.
        </div>
    </div>
</footer>
