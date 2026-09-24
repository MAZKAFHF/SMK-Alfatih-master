<x-admin.layouts.app :title="'Pendaftar PPDB'">
    @php
        $statusOptions = collect(\App\Enums\RegistrationStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->all();
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Total <strong class="font-semibold text-slate-900 dark:text-white">{{ number_format($registrations->total()) }}</strong> pendaftar
        </p>

        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('ppdb.index') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-700 hover:underline dark:text-primary-400">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                Lihat halaman PPDB
            </a>

            @if ($registrations->total() > 0)
                <x-ui.button
                    variant="danger"
                    size="sm"
                    data-confirm-title="Hapus semua data pendaftaran?"
                    data-confirm-message="Seluruh {{ number_format($registrations->total()) }} data pendaftaran akan dihapus secara permanen dan tidak dapat dikembalikan."
                    onclick="confirmDialog({ title: this.dataset.confirmTitle, message: this.dataset.confirmMessage, confirmText: 'Ya, Hapus Semua', formAction: '{{ route('admin.registrations.destroy-all') }}', method: 'DELETE' })"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus Semua
                </x-ui.button>
            @endif
        </div>
    </div>

    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <x-ui.input
                    name="search"
                    placeholder="Cari nama, nomor pendaftaran, email, atau no. HP..."
                    value="{{ request('search') }}"
                />
            </div>

            <div class="sm:w-56">
                <x-ui.select
                    name="status"
                    :value="request('status')"
                    :options="$statusOptions"
                    placeholder="Semua status"
                    :placeholder-option="false"
                >
                    <option value="" {{ blank(request('status')) ? 'selected' : '' }}>Semua status</option>
                </x-ui.select>
            </div>

            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>

            @if (request()->has('search') || request()->has('status'))
                <x-ui.button variant="ghost" href="{{ route('admin.registrations.index') }}">Reset</x-ui.button>
            @endif
        </form>
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
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-[820px] w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">No. Pendaftaran</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nama</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Program</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kontak</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th scope="col" class="sticky right-0 bg-slate-50 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 shadow-[calc(-8px_0_8px_-8px_rgba(15,23,42,0.15))] dark:bg-slate-800 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach ($registrations as $registration)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-xs font-medium text-slate-600 dark:text-slate-400">{{ $registration->registration_number }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $registration->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $registration->school_origin ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $registration->program?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                    <p>{{ $registration->phone ?? '—' }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $registration->email ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $registration->created_at->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :color="$registration->status->badgeColor()" size="sm" dot>{{ $registration->status->label() }}</x-ui.badge>
                                </td>
                                <td class="sticky right-0 bg-white px-4 py-3 text-right shadow-[calc(-8px_0_8px_-8px_rgba(15,23,42,0.15))] group-hover:bg-slate-50 dark:bg-slate-900 dark:group-hover:bg-slate-800/50">
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
