<x-admin.layouts.app :title="'Dashboard'">
    {{-- Hero / sambutan --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-700 via-primary-800 to-primary-950 p-6 text-white shadow-card sm:p-8">
        <div class="pointer-events-none absolute -right-10 -top-10 size-48 rounded-full bg-primary-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-10 size-56 rounded-full bg-accent-500/20 blur-3xl" aria-hidden="true"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-primary-200">{{ ucfirst(now()->translatedFormat('l, d F Y')) }}</p>
                <h2 class="mt-1.5 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-primary-100">
                    Pantau pendaftaran PPDB, verifikasi data calon siswa, dan kelola status pendaftaran dari satu tempat.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-400"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                    </svg>
                    Kelola Pendaftar
                </a>
                <a
                    href="{{ route('home') }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur transition-colors hover:bg-white/20"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
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
                    'href' => route('admin.registrations.index'),
                    'foot' => 'Lihat semua pendaftar',
                ],
                [
                    'label' => 'Diterima',
                    'value' => $stats['accepted'],
                    'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'tone' => 'bg-emerald-100 text-emerald-700',
                    'href' => route('admin.registrations.index', ['status' => 'accepted']),
                    'foot' => 'Pendaftar diterima',
                ],
                [
                    'label' => 'Perlu Diverifikasi',
                    'value' => $stats['pending'],
                    'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                    'tone' => 'bg-sky-100 text-sky-700',
                    'href' => route('admin.registrations.index', ['status' => 'pending']),
                    'foot' => 'Tunggu pengecekan Anda',
                ],
            ];
        @endphp

        @foreach ($cards as $card)
            <a href="{{ $card['href'] }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-card-hover dark:border-slate-800 dark:bg-slate-900">
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
            </a>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Tren 7 hari --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
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
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Sebaran Status</h3>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Semua pendaftar berdasarkan status</p>

            <div class="mt-6">
                @php $statusTotal = max($stats['total'], 1); @endphp

                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" role="img" aria-label="Grafik sebaran status pendaftaran">
                    @foreach ($statuses as $status)
                        @php
                            $segments = [
                                'pending' => 'bg-amber-400',
                                'accepted' => 'bg-emerald-500',
                                'rejected' => 'bg-red-400',
                                'cancelled' => 'bg-slate-300',
                            ];
                        @endphp
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

    {{-- Pilihan program & pendaftar terbaru --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Sebaran program --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
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
        <div class="rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5 dark:border-slate-700/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Pendaftar Terbaru</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Pendaftar yang baru saja mendaftar</p>
                </div>
                <div class="flex gap-2">
                    <x-ui.button variant="outline" size="sm" href="{{ route('admin.registrations.index') }}">Semua Pendaftar</x-ui.button>
                </div>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse ($recent as $registration)
                    <li class="group flex items-center gap-4 px-6 py-4 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
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
                        <x-ui.badge :color="$registration->status->badgeColor()" size="sm" dot>{{ $registration->status->label() }}</x-ui.badge>
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
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-700/50 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Aktivitas Login Terbaru</h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Admin yang baru saja masuk atau keluar</p>
                </div>
                <x-ui.button variant="outline" size="sm" href="{{ route('admin.login-logs.index') }}">Lihat Semua</x-ui.button>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($recentLoginLogs as $log)
                    <li class="flex items-center gap-4 px-6 py-4">
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
