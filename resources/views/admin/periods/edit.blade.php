<x-admin.layouts.app :title="'Ubah Periode PPDB'" :breadcrumb="[['label' => 'Periode PPDB', 'url' => route('admin.periods.index')], ['label' => $period->academic_year]]">
<x-admin.page-head title="Ubah Periode {{ $period->academic_year }}" context="Status: {{ $period->statusLabel() }}. Tahun ajaran periode berjalan dikunci." />
<x-ui.card class="p-6">
    <form method="POST" action="{{ route('admin.periods.update', $period) }}" class="grid gap-4 sm:grid-cols-2" novalidate>@csrf @method('PUT')
        <x-ui.input label="Tahun Ajaran *" name="academic_year" value="{{ old('academic_year', $period->academic_year) }}" required />
        <x-ui.input label="Kuota (opsional)" name="quota" type="number" value="{{ old('quota', $period->quota) }}" />
        <x-ui.datetime-picker label="Dibuka Pada (WIB)" name="opens_at" value="{{ old('opens_at', \App\Services\JakartaDateTime::forInput($period->opens_at)) }}" />
        <x-ui.datetime-picker label="Ditutup Pada (WIB)" name="closes_at" value="{{ old('closes_at', \App\Services\JakartaDateTime::forInput($period->closes_at)) }}" />
        <div class="sm:col-span-2"><x-ui.textarea label="Pengumuman" name="announcement" rows="2">{{ old('announcement', $period->announcement) }}</x-ui.textarea></div>
        <div class="sm:col-span-2"><x-ui.input label="Kontak Bantuan" name="contact_info" value="{{ old('contact_info', $period->contact_info) }}" /></div>
        <div class="sm:col-span-2 mt-2 border-t pt-5" style="border-color: var(--ctl-border);">
            <h2 class="font-bold">Lifecycle Akun Pemohon</h2>
            <p class="mt-1 text-sm text-[var(--ctl-text-muted)]">Dikelola otomatis oleh sistem. Tandai Selesai dari daftar periode setelah seluruh pekerjaan beres.</p>
            @php
                $retentionDays = (int) config('retention.applicants.real_retention_days', 90);
                $fmt = fn ($dt) => $dt ? $dt->copy()->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i').' WIB' : 'Belum';
            @endphp
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="font-semibold">Hasil diumumkan</dt><dd>{{ $fmt($period->results_released_at) }}</dd></div>
                <div><dt class="font-semibold">Operasional selesai</dt><dd>{{ $fmt($period->operational_completed_at) }}</dd></div>
                <div><dt class="font-semibold">Retensi</dt><dd>{{ $retentionDays }} hari</dd></div>
                <div><dt class="font-semibold">Cleanup akun</dt><dd>Otomatis oleh sistem</dd></div>
                <div class="sm:col-span-2"><dt class="font-semibold">Eligible mulai</dt><dd>{{ $fmt($period->account_retention_until) }}</dd></div>
            </dl>
        </div>
        <x-ui.validation-summary />
        <div class="sm:col-span-2 flex justify-end gap-2">
            <x-ui.button variant="ghost" href="{{ route('admin.periods.index') }}">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>
</x-admin.layouts.app>
