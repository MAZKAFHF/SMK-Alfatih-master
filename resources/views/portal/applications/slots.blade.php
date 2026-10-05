<x-portal.layouts.app :title="'Pilih Jadwal — '.$application->name">
<div class="max-w-5xl">
    <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-700 dark:text-primary-300">Tahap Wawancara</p>
    <h1 class="mt-1 font-display text-2xl font-extrabold sm:text-3xl">Pilih Jadwal Wawancara</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $application->name }} — {{ $application->registration_number }}</p>

    @if($slots->isEmpty())
        <div class="mt-5"><x-ui.alert variant="info" title="Belum ada jadwal">Belum ada jadwal wawancara yang tersedia saat ini. Silakan periksa kembali atau hubungi Admin PPDB.</x-ui.alert></div>
    @else
        <div class="mt-5 flex items-start gap-3 rounded-xl border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-900 dark:border-primary-800 dark:bg-primary-950/40 dark:text-primary-100">
            <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 10v6m0-9h.01"/></svg>
            <p><strong>Pilih satu jadwal.</strong> Kartu yang dipilih akan berubah hijau dan menampilkan tanda centang sebelum kamu melakukan konfirmasi.</p>
        </div>

        <form method="POST" novalidate action="{{ route('portal.slots.book', $application) }}" class="mt-5 space-y-4" data-slot-form>
            @csrf
            @foreach($slots->groupBy(fn($s) => ucfirst($s->date->translatedFormat('l, d F Y'))) as $day => $daySlots)
                <x-ui.card class="p-4 sm:p-5">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-3 dark:border-slate-700">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z"/></svg>
                        </span>
                        <h2 class="font-display text-base font-bold sm:text-lg">{{ $day }}</h2>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($daySlots as $slot)
                            @php
                                $available = $slot->remaining() > 0;
                                $selected = $available && (string) old('slot_id') === (string) $slot->id;
                                $summary = ucfirst($slot->date->translatedFormat('l, d F Y')).' • '.$slot->start_time.' WIB • '.($slot->location ?: 'Lokasi menyusul');
                            @endphp
                            <label
                                class="portal-slot-option {{ $available ? '' : 'is-disabled' }}"
                                data-slot-option
                                data-slot-summary="{{ $summary }}"
                                data-selected="{{ $selected ? 'true' : 'false' }}"
                                aria-checked="{{ $selected ? 'true' : 'false' }}"
                            >
                                <input type="radio" name="slot_id" value="{{ $slot->id }}" class="sr-only" data-slot-input @checked($selected) @disabled(!$available)>
                                <span class="portal-slot-check" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 10 3 3 7-7"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <strong class="font-display text-lg">{{ $slot->start_time }} WIB</strong>
                                        <span class="portal-slot-selected-label">Dipilih</span>
                                    </span>
                                    <span class="mt-1 flex items-start gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <svg class="mt-0.5 size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6-5.25 6-11a6 6 0 1 0-12 0c0 5.75 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></svg>
                                        <span>{{ $slot->location ?: 'Lokasi menyusul' }}</span>
                                    </span>
                                    <span class="mt-2 inline-flex rounded-full px-2 py-1 text-[11px] font-bold {{ $available ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' : 'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300' }}">
                                        {{ $available ? $slot->remaining().' tempat tersisa' : 'Slot penuh' }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-ui.card>
            @endforeach

            <x-ui.validation-summary title="Gagal booking" />

            <div class="hidden rounded-2xl border border-primary-300 bg-white p-4 shadow-sm dark:border-primary-800 dark:bg-slate-900" data-slot-summary-panel aria-live="polite">
                <div class="flex items-start gap-3">
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                        <svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 10 3 3 7-7"/></svg>
                    </span>
                    <div><p class="text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">Jadwal yang kamu pilih</p><p class="mt-1 font-semibold" data-slot-summary-text></p></div>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400" data-slot-help>Pilih salah satu kartu jadwal untuk melanjutkan.</p>
                <x-ui.button type="submit" data-slot-submit disabled>Konfirmasi Jadwal Pilihan</x-ui.button>
            </div>
        </form>
    @endif
</div>
</x-portal.layouts.app>
