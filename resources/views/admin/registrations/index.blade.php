<x-admin.layouts.app :title="'Pendaftar PPDB'">
    @php $ctxPeriod = ($activePeriodId ?? null) ? $periods->firstWhere('id', $activePeriodId) : null; @endphp
    <x-admin.page-head title="Pendaftar PPDB" context="{{ $ctxPeriod ? 'PPDB '.$ctxPeriod->academic_year.' • ' : '' }}Total {{ number_format($registrations->total()) }} pendaftar.">
        @if(!empty($isHistoryView))
            <x-ui.badge color="slate" size="sm">Riwayat — baca saja</x-ui.badge>
        @endif
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button size="sm" href="{{ route('admin.registrations.create') }}">+ Entri Manual</x-ui.button>
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('ppdb.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-700 hover:underline dark:text-primary-400">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                Lihat halaman PPDB
            </a>

        </div>
        </div>
    </x-admin.page-head>

    @isset($counts)
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach([['Total', $counts['total'] ?? 0], ['Menunggu Verifikasi', $counts['submitted'] ?? 0], ['Perlu Perbaikan', $counts['needs_revision'] ?? 0], ['Terverifikasi', $counts['verified'] ?? 0], ['Menunggu Keputusan', $counts['waiting_decision'] ?? 0]] as [$label, $val])
        <x-ui.card class="!p-3"><p class="ctl-faint text-[11px] uppercase tracking-wider">{{ $label }}</p><p class="text-xl font-extrabold">{{ number_format($val) }}</p></x-ui.card>
        @endforeach
    </div>
    @endisset

    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="grid gap-3 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <x-ui.input
                    name="search"
                    placeholder="Cari nama, nomor, NIK, NISN, ortu, email, HP..."
                    value="{{ request('search') }}"
                />
            </div>

            <div>
                <x-ui.select name="application_status" :value="request('application_status')" :options="['' => 'Semua status'] + collect(\App\Enums\ApplicationStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->all()" placeholder="Status aplikasi" :placeholder-option="false"></x-ui.select>
            </div>

            <div>
                <x-ui.select name="program_id" :value="request('program_id')" :options="['' => 'Semua program'] + ($programs ?? collect())->pluck('name','id')->all()" placeholder="Program" :placeholder-option="false"></x-ui.select>
            </div>

            <div>
                <x-ui.select name="period_id" :value="$activePeriodId" :options="($periods ?? collect())->mapWithKeys(fn($p) => [$p->id => $p->academic_year.' — '.\App\Models\PpdbPeriod::statusLabelFor($p->status)])->all()" placeholder="Periode" :placeholder-option="false"></x-ui.select>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                @if (request()->query())
                    <x-ui.button variant="ghost" href="{{ route('admin.registrations.index') }}">Reset</x-ui.button>
                @endif
            </div>
        </form>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <a href="{{ route('admin.slots.index') }}" class="font-semibold text-primary-700">Kelola Slot &rarr;</a>
            <a href="{{ route('admin.registrations.export', request()->query()) }}" class="font-semibold text-primary-700">Export CSV terfilter &rarr;</a>
            <a href="{{ route('admin.registrations.print-list', request()->query()) }}" target="_blank" class="font-semibold text-primary-700">Cetak Daftar Terfilter &rarr;</a>
        </div>
    </x-ui.card>

    @if ($registrations->isEmpty())
        <x-ui.card class="p-6">
            <x-ui.empty-state
                title="Tidak ada data"
                description="Tidak ada data pendaftaran yang cocok dengan pencarian atau filter Anda."
            />
        </x-ui.card>
    @else
        <p class="mb-2 flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 lg:hidden" aria-hidden="true">
            <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
            </svg>
            Geser tabel ke samping untuk melihat data &amp; tombol detail
        </p>

        {{-- Tabel dengan scroll horizontal di layar kecil --}}
        <div class="-mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="ctl-table-wrap">
                <table class="ctl-table min-w-[820px]">
                    <thead>
                        <tr>
                            <th scope="col">No. Pendaftaran</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Program</th>
                            <th scope="col">Kontak</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="!text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registrations as $registration)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs font-medium">{{ $registration->registration_number }}</td>
                                <td>
                                    <p class="font-semibold">{{ $registration->name }}</p>
                                    <p class="ctl-muted text-xs">{{ $registration->school_origin ?? '—' }}</p>
                                </td>
                                <td>{{ $registration->program?->name ?? '—' }}</td>
                                <td class="text-sm">
                                    <p>{{ $registration->phone ?? '—' }}</p>
                                    <p class="ctl-faint text-xs">{{ $registration->email ?? '' }}</p>
                                </td>
                                <td class="whitespace-nowrap">{{ $registration->created_at->translatedFormat('d M Y') }}</td>
                                <td>
                                    <x-ui.badge :color="$registration->application_status->badgeColor()" size="sm" dot>{{ $registration->application_status->label() }}</x-ui.badge>
                                </td>
                                <td class="!text-right">
                                    <x-ui.button variant="outline" size="sm" href="{{ route('admin.registrations.show', $registration) }}">Detail</x-ui.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($registrations->hasPages())
        <div class="mt-6">
            {{ $registrations->links() }}
        </div>
    @endif
</x-admin.layouts.app>
