@php
    $footerAddress = \App\Models\SiteSetting::get('school_address');
    $footerPhone = \App\Models\SiteSetting::get('school_phone');
    $footerEmail = \App\Models\SiteSetting::get('school_email');
@endphp
<footer class="footer-glow relative overflow-hidden border-t border-forest-800 bg-forest-900 text-slate-300 dark:border-slate-800 dark:bg-slate-950">
    <x-motif-geometric class="absolute -right-10 -top-10 size-48 text-tech-500/15" />
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 pt-3 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-brand-logo data-footer-logo class="ring-white/20" />
                    <span class="leading-tight">
                        <span class="block font-display text-sm font-extrabold tracking-tight text-white">SMK TAHFIZH</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-gold-400">Al-Fatih</span>
                    </span>
                </a>
                <p class="mt-4 text-sm leading-relaxed text-slate-300">
                    Ruang pendidikan kejuruan dan tahfizh Al-Qur'an untuk tumbuh, belajar, dan membangun masa depan.
                </p>
                <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.2em] text-gold-500">Build • Character • Future</p>
                <div class="mt-5">
                    <x-social-links variant="footer" title="Ikuti SMK Tahfizh Al-Fatih" />
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Tautan Cepat</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ([
                        ['Beranda', route('home')],
                        ['Program Keahlian', route('programs.index')],
                        ['Berita', route('news.index')],
                        ['Galeri', route('gallery.index')],
                        ['Kontak', route('contact.index')],
                    ] as [$label, $url])
                        <li>
                            <a href="{{ $url }}" class="text-slate-300 transition-colors hover:text-gold-400">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">PPDB</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @if(\App\Services\PpdbAvailability::resolvePublic()->portalEntryVisible())
                    <li><a href="{{ route('portal.register') }}" class="text-slate-300 transition-colors hover:text-gold-400">Daftar PPDB</a></li>
                    <li><a href="{{ route('portal.login') }}" class="text-slate-300 transition-colors hover:text-gold-400">Masuk Portal</a></li>
                    @endif
                    <li><a href="{{ route('announcements.index') }}" class="text-slate-300 transition-colors hover:text-gold-400">Pengumuman</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Kontak</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-300">
                    @if(filled($footerAddress))<li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>{{ $footerAddress }}</span>
                    </li>@endif
                    @if(filled($footerPhone))<li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        <span>{{ $footerPhone }}</span>
                    </li>@endif
                    @if(filled($footerEmail))<li class="flex gap-2.5">
                        <svg class="mt-0.5 size-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span>{{ $footerEmail }}</span>
                    </li>@endif
                    @if(blank($footerAddress) && blank($footerPhone) && blank($footerEmail))
                        <li><a href="{{ route('contact.index') }}" class="font-semibold text-gold-400 hover:text-gold-300">Buka halaman kontak →</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-2 border-t border-white/10 pt-6 text-xs text-slate-400 sm:flex-row sm:text-left">
            <p>&copy; {{ date('Y') }} SMK Tahfizh Al-Fatih. Seluruh hak cipta dilindungi.</p>
            {{-- Developer credit: teks biasa (bukan link) sampai website resmi Mafh tersedia.
                 Untuk menjadikannya link nanti, ganti <span> menjadi <a href="...">. --}}
            <p class="transition-colors hover:text-gold-400">Developed by <strong class="font-semibold text-slate-300">Mafh</strong></p>
        </div>
    </div>
</footer>
