<x-admin.layouts.app :title="'Slot Wawancara'" :breadcrumb="[['label' => 'PPDB', 'url' => route('admin.registrations.index')], ['label' => 'Slot Wawancara']]">
@php $slotPeriod = ($activePeriodId ?? null) ? $periods->firstWhere('id', $activePeriodId) : null; @endphp
<x-admin.page-head title="Slot Wawancara" context="{{ $slotPeriod ? 'PPDB '.$slotPeriod->academic_year.' • ' : '' }}Jadwal yang dapat dipilih calon siswa terverifikasi. Tanpa double booking.">
    <form method="GET" action="{{ route('admin.slots.index') }}" class="w-full sm:w-56" id="slot-period-filter">
        <x-ui.select name="period_id" :value="$activePeriodId" :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->academic_year.' — '.\App\Models\PpdbPeriod::statusLabelFor($p->status)])->all()" placeholder="Periode" :placeholder-option="false" />
    </form>
    <x-ui.button size="sm" onclick="openModal('slot-create-modal')" :disabled="!$slotPeriod || $slotPeriod->isLockedForOperations()">
        <x-admin.icon name="plus" class="size-4" /> Buat Slot
    </x-ui.button>
</x-admin.page-head>
@push('scripts')
<script>
document.querySelector('#slot-period-filter input[name="period_id"]')?.addEventListener('change', (e) => e.target.form.submit());
</script>
@endpush

@php
    $available = $slots->getCollection()->filter(fn($s) => $s->status === 'active' && $s->date->isFuture())->count();
    $filled = $slots->getCollection()->sum(fn($s) => min($s->appointments_count, $s->capacity));
@endphp

<div class="mb-6 grid grid-cols-3 gap-3">
    <x-ui.card class="!p-4">
        <p class="ctl-faint text-xs font-semibold uppercase tracking-wider">Hari ini</p>
        <p class="mt-1 font-display text-2xl font-extrabold">{{ $today->count() }} <span class="text-sm font-medium ctl-muted">wawancara</span></p>
    </x-ui.card>
    <x-ui.card class="!p-4">
        <p class="ctl-faint text-xs font-semibold uppercase tracking-wider">Slot tersedia</p>
        <p class="mt-1 font-display text-2xl font-extrabold">{{ $available }}</p>
    </x-ui.card>
    <x-ui.card class="!p-4">
        <p class="ctl-faint text-xs font-semibold uppercase tracking-wider">Kursi terisi</p>
        <p class="mt-1 font-display text-2xl font-extrabold">{{ $filled }}</p>
    </x-ui.card>
</div>

@if($today->isNotEmpty())
<x-ui.card class="mb-6 !p-0">
    <h2 class="ctl-section-title px-5 pt-4">Wawancara Hari Ini <span class="ctl-faint text-sm font-medium">({{ now('Asia/Jakarta')->translatedFormat('d M Y') }})</span></h2>
    <ul class="divide-y px-2 pb-2" style="border-color: var(--ctl-border);">
        @foreach($today as $a)
        <li class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 hover:bg-[var(--ctl-row-hover)]">
            <span class="min-w-0 text-sm"><strong class="font-mono">{{ $a->slot->start_time }}</strong> — <span class="font-semibold">{{ $a->application->name }}</span> <span class="ctl-faint text-xs">{{ $a->application->registration_number }}</span></span>
            <a href="{{ route('admin.registrations.show', $a->application) }}" class="ctl-btn ctl-btn-ghost ctl-btn-sm shrink-0">Buka</a>
        </li>
        @endforeach
    </ul>
</x-ui.card>
@endif

<x-ui.card class="!p-0 overflow-hidden">
    <h2 class="ctl-section-title px-5 pt-4">Slot Mendatang</h2>
    <div class="mt-2 overflow-x-auto">
        <table class="ctl-table">
            <thead><tr><th>Tanggal</th><th>Waktu</th><th>Lokasi</th><th>Kapasitas</th><th>Status</th><th class="!text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($slots as $s)
                <tr>
                    <td class="whitespace-nowrap font-medium">{{ $s->date->translatedFormat('d M Y') }}</td>
                    <td class="whitespace-nowrap font-mono text-[13px]">{{ $s->start_time }}@if($s->end_time)–{{ $s->end_time }}@endif</td>
                    <td>{{ $s->location ?? '—' }}</td>
                    <td class="whitespace-nowrap">{{ $s->appointments_count }} / {{ $s->capacity }}</td>
                    <td><x-ui.badge :color="$s->status === 'active' ? 'green' : 'slate'" size="sm" dot>{{ $s->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-ui.badge></td>
                    <td class="!text-right">
                        <div class="inline-flex gap-1.5">
                            <form method="POST" action="{{ route('admin.slots.toggle', $s) }}" class="inline">@csrf
                                <button type="submit" class="ctl-btn ctl-btn-ghost ctl-btn-sm">{{ $s->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            @if($s->appointments_count === 0)
                            <button type="button" class="ctl-btn ctl-btn-ghost ctl-btn-sm" style="color: var(--ctl-danger);" onclick="confirmDialog({ title: 'Hapus slot?', message: 'Slot {{ $s->date->format('d M Y') }} {{ $s->start_time }} akan dihapus.', formAction: '{{ route('admin.slots.destroy', $s) }}', method: 'DELETE' })">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><x-ui.empty-state title="Belum ada slot" description="Buat slot pertama agar calon siswa terverifikasi dapat memilih jadwal." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($slots->hasPages())<div class="p-4">{{ $slots->links() }}</div>@endif
</x-ui.card>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#slot-create-modal .ctl-error-text')) openModal('slot-create-modal');
});
</script>
@endpush

<x-ui.modal id="slot-create-modal" title="Buat Slot Wawancara" size="md">
    <x-slot:subtitle>Slot langsung terlihat oleh calon siswa terverifikasi.</x-slot:subtitle>
    <form method="POST" action="{{ route('admin.slots.store') }}" class="space-y-4">@csrf
        <input type="hidden" name="period_id" value="{{ old('period_id', $activePeriodId) }}">
        <x-ui.date-picker label="Tanggal *" name="date" :value="old('date', now('Asia/Jakarta')->toDateString())" :min="now('Asia/Jakarta')->toDateString()" required />
        <div class="grid grid-cols-2 gap-3">
            <x-ui.time-picker label="Waktu Mulai *" name="start_time" value="{{ old('start_time', '08:00') }}" required />
            <x-ui.time-picker label="Waktu Selesai" name="end_time" value="{{ old('end_time') }}" />
        </div>
        <x-ui.input label="Lokasi" name="location" value="{{ old('location') }}" placeholder="Ruang Interview Lt.2" />
        <x-ui.input label="Kapasitas *" name="capacity" type="number" value="{{ old('capacity', 1) }}" help="Calon siswa per slot. Umumnya 1." required />
        <x-ui.textarea label="Catatan" name="notes" rows="2">{{ old('notes') }}</x-ui.textarea>
        <x-ui.validation-summary />
        <div class="flex justify-end gap-2">
            <x-ui.button variant="ghost" onclick="closeModal('slot-create-modal')">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Slot</x-ui.button>
        </div>
    </form>
</x-ui.modal>
</x-admin.layouts.app>
