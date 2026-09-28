<x-admin.layouts.app :title="'Dashboard'">
    {{-- Konteks periode PPDB (selector + mode). Semua angka di bawah milik periode ini. --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            @if($period)
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="ctl-page-title">PPDB {{ $period->academic_year }}</h2>
                    <x-ui.badge :color="$period->badgeColor()" size="sm" dot>{{ $modeLabel }}</x-ui.badge>
                </div>
                <p class="ctl-muted mt-1 text-sm">
                    @if($period->opens_at || $period->closes_at)
                        {{ $period->opens_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }} — {{ $period->closes_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }} WIB
                    @else
                        Periode tanpa batas tanggal
                    @endif
                    @if($period->quota !== null)
                        • Kuota: {{ \App\Services\PpdbAvailability::usedQuota($period->id) }} / {{ $period->quota }} terisi
                    @endif
                </p>
            @else
                <h2 class="ctl-page-title">Dashboard</h2>
                <p class="ctl-muted mt-1 text-sm">Belum ada periode PPDB.</p>
            @endif
        </div>
        @if(count($periodOptions) > 0)
            <form method="GET" action="{{ route('admin.dashboard') }}" class="w-full sm:w-64" id="period-selector">
                <x-ui.select name="period" :value="$period?->id" :options="collect($periodOptions)->mapWithKeys(fn($o) => [$o['id'] => $o['academic_year'].' — '.\App\Models\PpdbPeriod::statusLabelFor($o['status'])])->all()" placeholder="Pilih periode" :placeholder-option="false" />
            </form>
            @push('scripts')
            <script>
            document.querySelector('#period-selector input[name="period"]')?.addEventListener('change', (e) => e.target.form.submit());
            </script>
            @endpush
        @endif
    </div>

    @if($mode === 'history' && $period)
        <div class="mb-5 rounded-xl border p-4 text-sm" style="border-color: var(--ctl-border-strong); background: var(--ctl-sunken);" role="status">
            <strong>RIWAYAT PPDB {{ $period->academic_year }} — Periode telah selesai.</strong>
            <span class="ctl-muted">Menampilkan ringkasan periode terakhir. Aksi operasional dinonaktifkan; gunakan Lihat Pendaftar / Cetak / Export.</span>
        </div>
    @endif

    @if($mode === 'upcoming' && $period)
        <div class="mb-5 rounded-xl border p-4 text-sm" style="border-color: color-mix(in srgb, var(--ctl-info) 40%, transparent); background: var(--ctl-info-soft);" role="status">
            <strong>PPDB {{ $period->academic_year }} dibuka {{ $period->opens_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB.</strong>
            <span class="ctl-muted">Siapkan slot wawancara dan periksa pengaturan.</span>
        </div>
    @endif

    @if($mode === 'empty')
        <x-ui.card class="mb-5 p-6">
            <x-ui.empty-state title="Belum ada periode PPDB" description="Buat periode pertama untuk memulai penerimaan siswa baru.">
                <x-slot:action><x-ui.button href="{{ route('admin.periods.create') }}">+ Buat Periode PPDB</x-ui.button></x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @endif

    {{-- Konsol hari ini — Deep Forest di KEDUA tema (token), teks & aksi kontras --}}
    <div class="relative overflow-hidden rounded-xl px-5 py-5 sm:px-6" style="background: var(--ctl-sidebar); color: #ffffff; box-shadow: var(--ctl-shadow);">
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-primary-500 via-gold-500 to-energy-500" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-sm font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="leading-tight">
                    <p class="text-xs font-semibold uppercase tracking-widest" style="color: var(--ctl-side-muted);">{{ ucfirst(now()->translatedFormat('l, d F Y')) }} · Hari ini</p>
                    <p class="mt-0.5 text-base font-bold text-white">
                        {{ number_format($stats['needs_verification']) }} menunggu verifikasi
                        <span class="font-medium" style="color: var(--ctl-side-muted);" aria-hidden="true">·</span>
                        <span class="font-medium" style="color: var(--ctl-side-text);">{{ number_format($stats['total']) }} total pendaftar</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:shrink-0">
                @if($mode === 'active' || $mode === 'upcoming')
                <a
                    href="{{ route('admin.registrations.index', array_filter(['application_status' => 'submitted', 'period' => $period?->id])) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-energy-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-energy-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-energy-500"
                >
                    Verifikasi Sekarang
                </a>
                <a href="{{ route('admin.slots.index', array_filter(['period' => $period?->id])) }}" class="ctl-banner-ghost inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold">Kelola Slot</a>
                @else
                <a
                    href="{{ route('admin.registrations.index', array_filter(['period' => $period?->id])) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-energy-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-energy-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-energy-500"
                >
                    Lihat Pendaftar
                </a>
                @endif
                <a
                    href="{{ route('home') }}"
                    target="_blank"
                    rel="noopener"
                    class="ctl-banner-ghost inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold"
                >
                    Lihat Website
                </a>
            </div>
        </div>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $cards = [
                [
                    'label' => 'Total Pendaftar',
                    'value' => $stats['total'],
                    'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
                    'tone' => 'bg-primary-100 text-primary-700',
                    'href' => route('admin.registrations.index', $period ? ['period' => $period->id] : []),
                    'foot' => 'Lihat semua pendaftar',
                ],
                [
                    'label' => 'Lulus',
                    'value' => $stats['passed'],
                    'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'tone' => 'bg-emerald-100 text-emerald-700',
                    'href' => route('admin.registrations.index', array_filter(['application_status' => 'passed', 'period' => $period?->id])),
                    'foot' => 'Hasil resmi telah ditetapkan',
                ],
                [
                    'label' => 'Perlu Diverifikasi',
                    'value' => $stats['needs_verification'],
                    'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                    'tone' => 'bg-sky-100 text-sky-700',
                    'href' => route('admin.registrations.index', array_filter(['application_status' => 'submitted', 'period' => $period?->id])),
                    'foot' => 'Tunggu pengecekan Anda',
                ],
            ];
        @endphp

        @foreach ($cards as $card)
            <x-ui.card as="a" href="{{ $card['href'] }}" class="group p-5 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="flex size-11 items-center justify-center rounded-xl {{ $card['tone'] }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    </span>
                    <svg class="size-4 text-slate-300 transition-colors group-hover:text-primary-500 dark:text-slate-600 dark:group-hover:text-primary-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ number_format($card['value']) }}</p>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ $card['foot'] }}</p>
            </x-ui.card>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Tren 7 hari --}}
        <div class="ctl-card !p-6 xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tren Pendaftar</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Jumlah pendaftar masuk dalam 7 hari terakhir</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-950 dark:text-primary-400">
                    <span class="size-2 rounded-full bg-primary-500 dark:bg-primary-400" aria-hidden="true"></span>
                    {{ array_sum($last7Days->pluck('count')->all()) }} masuk
                </span>
            </div>
            @if(array_sum($last7Days->pluck('count')->all()) === 0)
                <p class="ctl-muted mt-4 text-sm">Belum ada pendaftar pada periode ini.</p>
            @endif

            <div class="mt-6 flex h-44 items-end gap-1.5 sm:gap-3">
                @foreach ($last7Days as $day)
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-bold text-slate-700 transition-colors group-hover:text-primary-600 dark:text-slate-300 dark:group-hover:text-primary-400">{{ $day['count'] }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div
                                class="w-full rounded-t-lg bg-gradient-to-t from-primary-600 to-primary-400 transition-all duration-300 group-hover:from-primary-700 group-hover:to-primary-500"
                                style="height: {{ max($day['count'] / $maxTrend * 100, 3) }}%"
                                title="{{ $day['full'] }}: {{ $day['count'] }} pendaftar"
                            ></div>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sebaran status --}}
        <div class="ctl-card !p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Sebaran Status</h3>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Semua pendaftar berdasarkan status</p>

            <div class="mt-6">
                @php $statusTotal = max($stats['total'], 1); @endphp

                @php
                    $segments = [
                        'draft' => 'bg-slate-300', 'submitted' => 'bg-amber-400',
                        'needs_revision' => 'bg-orange-400', 'resubmitted' => 'bg-amber-500',
                        'verified' => 'bg-emerald-400', 'waiting_slot' => 'bg-sky-400',
                        'scheduled' => 'bg-blue-500', 'interviewed' => 'bg-violet-400',
                        'waiting_decision' => 'bg-violet-600', 'passed' => 'bg-emerald-600',
                        'not_passed' => 'bg-red-500', 'cancelled' => 'bg-slate-500',
                    ];
                @endphp
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" role="img" aria-label="Grafik sebaran status pendaftaran">
                    @foreach ($statuses as $status)
                        @if ($stats[$status->value] > 0)
                            <div class="{{ $segments[$status->value] }}" style="width: {{ $stats[$status->value] / $statusTotal * 100 }}%"></div>
                        @endif
                    @endforeach
                </div>

                <ul class="mt-5 space-y-3">
                    @foreach ($statuses as $status)
                        <li class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <span class="size-2.5 rounded-full {{ $segments[$status->value] }}" aria-hidden="true"></span>
                                {{ $status->label() }}
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats[$status->value]) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Wawancara hari ini (periode ini) --}}
    <div class="mt-6 ctl-card !p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Wawancara Hari Ini</h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $period ? 'PPDB '.$period->academic_year : 'Tidak ada periode' }} • {{ now('Asia/Jakarta')->translatedFormat('d M Y') }}</p>
            </div>
            <x-ui.button variant="outline" size="sm" href="{{ route('admin.slots.index', $period ? ['period' => $period->id] : []) }}">Kelola Slot</x-ui.button>
        </div>
        @if($todayInterviews->isEmpty())
            <p class="ctl-muted mt-4 text-sm">Tidak ada wawancara hari ini pada periode ini.</p>
        @else
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($todayInterviews as $appt)
                    <li class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span><strong class="font-mono">{{ $appt->slot->start_time }}</strong> — {{ $appt->application->name }} <span class="ctl-faint text-xs">{{ $appt->application->registration_number }}</span></span>
                        <a href="{{ route('admin.registrations.show', $appt->application) }}" class="font-semibold text-primary-700 hover:underline dark:text-primary-400">Buka</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Pilihan program & pendaftar terbaru --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Sebaran program --}}
        <div class="ctl-card !p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Peminat Program Keahlian</h3>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Program paling banyak dipilih pendaftar</p>

            @php $programTotal = max($programDistribution->sum('total'), 1); @endphp

            <ul class="mt-6 space-y-5">
                @forelse ($programDistribution as $index => $program)
                    <li>
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate text-sm font-medium text-slate-700 dark:text-slate-300">{{ $program['name'] }}</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $program['total'] }}</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-gradient-to-r {{ ['from-primary-500 to-emerald-400', 'from-sky-500 to-sky-400', 'from-accent-500 to-accent-400', 'from-purple-500 to-violet-400', 'from-rose-500 to-rose-400'][$index % 5] }}"
                                style="width: {{ $program['total'] / $programTotal * 100 }}%"
                            ></div>
                        </div>
                    </li>
                @empty
                    <li>
                        <x-ui.empty-state
                            icon="false"
                            title="Belum ada data program"
                            description="Data peminat program akan tampil setelah ada pendaftar."
                        />
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Pendaftar terbaru --}}
        <div class="ctl-card !p-0 overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5 dark:border-slate-700/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Pendaftar Terbaru</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Pendaftar yang baru saja mendaftar</p>
                </div>
                <div class="flex gap-2">
                    <x-ui.button variant="outline" size="sm" href="{{ route('admin.registrations.index', $period ? ['period' => $period->id] : []) }}">Semua Pendaftar</x-ui.button>
                </div>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse ($recent as $registration)
                    <li class="group flex items-center gap-4 px-6 py-4 transition-colors hover:bg-[var(--ctl-row-hover)]">
                        <a href="{{ route('admin.registrations.show', $registration) }}" class="flex min-w-0 flex-1 items-center gap-4" aria-label="Detail {{ $registration->name }}">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-900 dark:text-primary-400">
                                {{ strtoupper(substr($registration->name, 0, 1)) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-slate-900 group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $registration->name }}</span>
                                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">
                                    {{ $registration->program?->name ?? 'Tanpa program' }} &middot; {{ $registration->created_at->diffForHumans() }}
                                </span>
                            </span>
                        </a>
                        <x-ui.badge :color="$registration->application_status->badgeColor()" size="sm" dot>{{ $registration->application_status->label() }}</x-ui.badge>
                        <a
                            href="{{ route('admin.registrations.show', $registration) }}"
                            class="hidden shrink-0 text-sm font-medium text-primary-700 transition-colors hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 sm:inline-flex sm:items-center sm:gap-1"
                        >
                            Detail
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-8">
                        <x-ui.empty-state
                            title="Belum ada pendaftar"
                            description="Belum ada pendaftaran yang masuk."
                        >
                            <x-slot:action>
                                <x-ui.button variant="outline" size="sm" href="{{ route('ppdb.index') }}" target="_blank">Buka Halaman PPDB</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Aktivitas login (superadmin) --}}
    @if ($recentLoginLogs->isNotEmpty())
        <div class="ctl-card !p-0 overflow-hidden mt-6">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-700/50 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Aktivitas Login Terbaru</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Admin yang baru saja masuk atau keluar</p>
                </div>
                <x-ui.button variant="outline" size="sm" href="{{ route('admin.login-logs.index') }}">Lihat Semua</x-ui.button>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($recentLoginLogs as $log)
                    <li class="flex items-center gap-4 px-6 py-4 transition-colors hover:bg-[var(--ctl-row-hover)]">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                            {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Pengguna terhapus' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ $log->ip_address ?? 'Tanpa IP' }} &middot; {{ $log->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <x-ui.badge :color="$log->event === \App\Models\LoginLog::EVENT_LOGIN ? 'green' : 'slate'" size="sm" dot>{{ $log->eventLabel() }}</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-admin.layouts.app>
