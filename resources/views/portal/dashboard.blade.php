<x-portal.layouts.app title="Beranda Portal PPDB">
    @php
        $focusApplication = $current->first();
        $focusAction = $focusApplication?->nextPortalAction();
    @endphp

    <section class="flex flex-col gap-5 border-b border-emerald-950/10 pb-7 dark:border-white/10 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            <p class="future-kicker">PPDB {{ $period?->academic_year ?: 'Sekolah' }}</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl">Selamat datang, {{ Illuminate\Support\Str::before(auth()->user()->name, ' ') }}.</h1>
            <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">Pantau setiap tahap pendaftaran dan selesaikan tindakan yang dibutuhkan dari satu ruang yang tenang dan jelas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-bold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                <span class="size-2 rounded-full {{ $availability->canRegister() ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
                {{ $availability->publicLabel() }}
            </span>
            @if($canRegister)
                <x-ui.button href="{{ route('portal.applications.create') }}">Tambah calon siswa</x-ui.button>
            @endif
        </div>
    </section>

    @unless(auth()->user()->hasVerifiedEmail())
        <section class="mt-6 flex flex-col gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between" role="status">
            <div class="flex gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-200/70 text-amber-800 dark:bg-amber-900 dark:text-amber-200" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0-8.659 5.549a2.25 2.25 0 01-2.182 0L2.25 6.75" /></svg>
                </span>
                <div>
                    <h2 class="font-bold">Verifikasi email untuk pengiriman final</h2>
                    <p class="mt-1 text-sm leading-relaxed text-amber-800 dark:text-amber-200">Tautan dikirim ke <strong>{{ auth()->user()->email }}</strong>. Anda tetap dapat menyimpan draf sambil menunggu verifikasi.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('portal.verification.resend') }}" class="shrink-0">@csrf
                <button class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-amber-900 px-4 text-sm font-bold text-white transition-colors hover:bg-amber-950 sm:w-auto">Kirim ulang email</button>
            </form>
        </section>
    @endunless

    @if($current->isEmpty() && $history->isEmpty())
        <section class="mt-7 grid gap-5 lg:grid-cols-[1.5fr_0.7fr]">
            <div class="portal-card relative overflow-hidden p-7 sm:p-9">
                <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-800/20" aria-hidden="true"></div>
                <div class="relative max-w-xl">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-primary-700 text-white shadow-lg shadow-emerald-900/10" aria-hidden="true">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21C7.043 21 4.862 20.355 3 19.234z" /></svg>
                    </span>
                    <p class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em] text-primary-700 dark:text-tech-400">Langkah pertama</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Belum ada data calon siswa.</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $canRegister ? 'Tambahkan calon siswa untuk mulai mengisi data. Draf dapat disimpan dan dilanjutkan kapan saja.' : $availability->portalNotice() }}</p>
                    @if($canRegister)<div class="mt-6"><x-ui.button size="lg" href="{{ route('portal.applications.create') }}">Mulai pendaftaran</x-ui.button></div>@endif
                </div>
            </div>
            <aside class="portal-card p-6">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Alur pendaftaran</p>
                <ol class="mt-5 space-y-4">
                    @foreach(['Data siswa', 'Dokumen', 'Verifikasi', 'Wawancara', 'Hasil'] as $step)
                        <li class="flex items-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-300"><span class="flex size-7 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 text-[11px] font-extrabold text-primary-700 dark:border-emerald-800 dark:bg-emerald-950">{{ $loop->iteration }}</span>{{ $step }}</li>
                    @endforeach
                </ol>
            </aside>
        </section>
    @else
        @if($focusApplication)
            <section class="mt-7 grid gap-5 xl:grid-cols-[1.65fr_0.75fr]">
                <div class="portal-card relative overflow-hidden p-6 sm:p-8">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-700 via-tech-500 to-gold-500" aria-hidden="true"></div>
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-primary-700 dark:text-tech-400">Tindakan berikutnya</p>
                            <h2 class="mt-2 truncate font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">{{ $focusApplication->name }}</h2>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $focusApplication->registration_number }} · {{ $focusApplication->program?->name }}</p>
                        </div>
                        <x-ui.badge :color="$focusApplication->application_status->badgeColor()" size="sm">{{ $focusApplication->application_status->label() }}</x-ui.badge>
                    </div>

                    <ol class="mt-7 grid grid-cols-5 gap-1" aria-label="Progres pendaftaran {{ $focusApplication->name }}">
                        @foreach($focusApplication->progressSteps() as $step)
                            <li class="relative text-center" @if($step['state'] === 'current') aria-current="step" @endif>
                                @unless($loop->first)<span class="absolute right-1/2 top-3.5 h-0.5 w-full -translate-y-1/2 {{ $step['state'] === 'done' ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700' }}" aria-hidden="true"></span>@endunless
                                <span class="relative mx-auto flex size-7 items-center justify-center rounded-full text-[10px] font-extrabold ring-4 ring-white dark:ring-[#102019] {{ $step['state']==='done' ? 'bg-emerald-500 text-white' : ($step['state']==='current' ? 'bg-primary-700 text-white' : ($step['state']==='attention' ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-300')) }}">{{ $step['state']==='done' ? '✓' : $loop->iteration }}</span>
                                <span class="mt-2 block truncate text-[10px] font-bold text-slate-500 sm:text-xs">{{ $step['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-7 flex flex-col gap-3 border-t border-emerald-950/10 pt-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500">Terakhir diperbarui {{ $focusApplication->updated_at->diffForHumans() }}</p>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <x-ui.button href="{{ $focusAction['route'] }}">{{ $focusAction['label'] }}</x-ui.button>
                            <x-ui.button variant="outline" href="{{ route('portal.applications.show', $focusApplication) }}">Lihat tahapan</x-ui.button>
                        </div>
                    </div>
                </div>

                <aside class="portal-card p-6">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Periode aktif</p>
                    <p class="mt-3 font-display text-2xl font-extrabold text-slate-950 dark:text-white">{{ $period?->academic_year ?: 'Belum tersedia' }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $availability->portalNotice() }}</p>
                    @if($notifications->isNotEmpty())
                        <a href="{{ route('portal.notifications') }}" class="mt-5 flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-primary-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                            <span>Notifikasi terbaru</span>
                            @if($unread)<span class="rounded-full bg-red-600 px-2 py-0.5 text-[10px] text-white">{{ $unread }} baru</span>@else<span aria-hidden="true">→</span>@endif
                        </a>
                    @endif
                </aside>
            </section>
        @endif

        <section class="mt-8" aria-labelledby="applications-heading">
            <div class="flex items-end justify-between gap-4">
                <div><p class="future-kicker">Pendaftaran</p><h2 id="applications-heading" class="mt-2 font-display text-xl font-extrabold text-slate-950 dark:text-white">Semua calon siswa</h2></div>
                @if($canRegister)<a href="{{ route('portal.applications.create') }}" class="text-sm font-bold text-primary-700 hover:underline dark:text-tech-400">Tambah siswa →</a>@endif
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($current as $app)
                    @php($nextAction = $app->nextPortalAction())
                    <x-ui.card class="p-5" hover>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><p class="truncate font-bold text-slate-950 dark:text-white">{{ $app->name }}</p><p class="mt-1 truncate font-mono text-[11px] text-slate-500">{{ $app->registration_number }} · {{ $app->program?->name }}</p></div>
                            <x-ui.badge :color="$app->application_status->badgeColor()" size="sm">{{ $app->application_status->label() }}</x-ui.badge>
                        </div>
                        <div class="mt-5 flex gap-2"><x-ui.button size="sm" href="{{ $nextAction['route'] }}">{{ $nextAction['label'] }}</x-ui.button><x-ui.button size="sm" variant="ghost" href="{{ route('portal.applications.show', $app) }}">Detail</x-ui.button></div>
                    </x-ui.card>
                @endforeach
            </div>
        </section>

        @if($history->isNotEmpty())
            <section class="mt-10 border-t border-emerald-950/10 pt-8 dark:border-white/10" aria-labelledby="history-heading">
                <h2 id="history-heading" class="text-sm font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat periode sebelumnya</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach($history as $app)
                        <x-ui.card class="flex items-center justify-between gap-4 p-5">
                            <div class="min-w-0"><p class="truncate font-bold text-slate-900 dark:text-white">{{ $app->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $app->period?->academic_year ?? '—' }} · {{ $app->program?->name }}</p></div>
                            <div class="flex shrink-0 flex-col items-end gap-2"><x-ui.badge :color="$app->application_status->badgeColor()" size="sm">{{ $app->application_status->label() }}</x-ui.badge><a href="{{ route('portal.applications.show', $app) }}" class="text-xs font-bold text-primary-700 hover:underline dark:text-tech-400">Lihat riwayat</a></div>
                        </x-ui.card>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</x-portal.layouts.app>
