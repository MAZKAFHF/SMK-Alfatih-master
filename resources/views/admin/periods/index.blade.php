<x-admin.layouts.app :title="'Periode PPDB'" :breadcrumb="[['label' => 'PPDB', 'url' => route('admin.registrations.index')], ['label' => 'Periode PPDB']]">
<x-admin.page-head title="Periode PPDB" context="Satu periode berjalan dalam satu waktu. Menutup periode mengarsipkan riwayat — data tidak dihapus.">
    <x-ui.button size="sm" href="{{ route('admin.periods.create') }}">
        <x-admin.icon name="plus" class="size-4" /> Buat Periode
    </x-ui.button>
</x-admin.page-head>

<x-ui.card class="!p-0 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="ctl-table min-w-[760px]">
            <thead><tr><th>Tahun Ajaran</th><th>Status</th><th>Jendela (WIB)</th><th>Kuota</th><th>Pendaftar</th><th class="!text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($periods as $p)
                <tr>
                    <td class="font-bold">{{ $p->academic_year }}</td>
                    <td><x-ui.badge :color="$p->badgeColor()" size="sm" dot>{{ $p->statusLabel() }}</x-ui.badge></td>
                    <td class="whitespace-nowrap text-[13px]">{{ $p->opens_at?->timezone('Asia/Jakarta')->format('d M Y') ?? '—' }} — {{ $p->closes_at?->timezone('Asia/Jakarta')->format('d M Y') ?? '—' }}</td>
                    <td class="whitespace-nowrap text-[13px]">@if($p->quota !== null){{ \App\Services\PpdbAvailability::usedQuota($p->id) }} / {{ $p->quota }}@else Tanpa batas @endif</td>
                    <td>{{ $p->applications_count }}</td>
                    <td class="!text-right">
                        <div class="inline-flex flex-wrap justify-end gap-1.5">
                            <a href="{{ route('admin.dashboard', ['period' => $p->id]) }}" class="ctl-btn ctl-btn-ghost ctl-btn-sm">Dasbor</a>
                            @if($p->status !== 'completed')
                            <a href="{{ route('admin.periods.edit', $p) }}" class="ctl-btn ctl-btn-ghost ctl-btn-sm">Ubah</a>
                            @endif
                            @if(in_array($p->status, ['draft', 'upcoming']))
                            <form method="POST" action="{{ route('admin.periods.open', $p) }}" class="inline" data-period-open-form>@csrf<button type="submit" class="ctl-btn ctl-btn-ghost ctl-btn-sm">Buka</button></form>
                            @endif
                            @if($p->status === 'open')
                            <button type="button" class="ctl-btn ctl-btn-ghost ctl-btn-sm" onclick="confirmDialog({ title: 'Tutup periode {{ $p->academic_year }}?', message: 'Pendaftar tidak dapat mendaftar lagi. Data tersimpan sebagai riwayat dan dashboard beralih ke mode riwayat.', confirmText: 'Ya, Tutup', formAction: '{{ route('admin.periods.close', $p) }}', method: 'POST' })">Tutup</button>
                            @endif
                            @if(in_array($p->status, ['closed']) && auth()->user()?->is_superadmin)
                            <form method="POST" action="{{ route('admin.periods.reopen', $p) }}" class="inline">@csrf<button type="submit" class="ctl-btn ctl-btn-ghost ctl-btn-sm">Buka Lagi</button></form>
                            <button type="button" class="ctl-btn ctl-btn-ghost ctl-btn-sm" onclick="confirmDialog({ title: 'SELESAIKAN PPDB {{ $p->academic_year }}?', message: 'Pastikan seluruh verifikasi, wawancara, keputusan, dan pengumuman hasil telah selesai. Setelah periode diselesaikan, akun pendaftar yang tidak lagi diperlukan akan dihapus otomatis dan tidak dapat digunakan untuk login kembali. Riwayat PPDB periode ini tetap disimpan.', confirmText: 'Tandai Selesai', cancelText: 'Batal', formAction: '{{ route('admin.periods.complete', $p) }}', method: 'POST', fields: { confirm: '1' } })">Selesaikan</button>
                            @endif
                            @if(in_array($p->status, ['closed', 'draft']))
                            <form method="POST" action="{{ route('admin.periods.archive', $p) }}" class="inline">@csrf<button type="submit" class="ctl-btn ctl-btn-ghost ctl-btn-sm">Arsip</button></form>
                            @endif
                            @if($p->applications_count === 0 && auth()->user()?->is_superadmin)
                            <button type="button" class="ctl-btn ctl-btn-ghost ctl-btn-sm" style="color: var(--ctl-danger);" onclick="confirmDialog({ title: 'Hapus periode?', message: 'Periode {{ $p->academic_year }} (kosong) akan dihapus permanen.', formAction: '{{ route('admin.periods.destroy', $p) }}', method: 'DELETE' })">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><x-ui.empty-state title="Belum ada periode" description="Buat periode pertama untuk memulai PPDB." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($periods->hasPages())<div class="p-4">{{ $periods->links() }}</div>@endif
</x-ui.card>
</x-admin.layouts.app>
