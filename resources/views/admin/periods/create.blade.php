<x-admin.layouts.app :title="'Buat Periode PPDB'" :breadcrumb="[['label' => 'Periode PPDB', 'url' => route('admin.periods.index')], ['label' => 'Buat']]">
<x-admin.page-head title="Buat Periode PPDB" context="Periode baru mulai dari nol pendaftar. Periode berjalan lain harus ditutup dulu." />
<x-ui.card class="p-6">
    <form method="POST" action="{{ route('admin.periods.store') }}" class="grid gap-4 sm:grid-cols-2" novalidate>@csrf
        <x-ui.input label="Tahun Ajaran *" name="academic_year" value="{{ old('academic_year') }}" placeholder="2027/2028" required help="Format bebas, unik. Contoh: 2027/2028." />
        <x-ui.input label="Kuota (opsional)" name="quota" type="number" value="{{ old('quota') }}" help="Kosongkan = tanpa batas kuota." />
        <x-ui.datetime-picker label="Dibuka Pada (WIB)" name="opens_at" value="{{ old('opens_at') }}" />
        <x-ui.datetime-picker label="Ditutup Pada (WIB)" name="closes_at" value="{{ old('closes_at') }}" />
        <div class="sm:col-span-2"><x-ui.textarea label="Pengumuman" name="announcement" rows="2">{{ old('announcement') }}</x-ui.textarea></div>
        <div class="sm:col-span-2"><x-ui.input label="Kontak Bantuan" name="contact_info" value="{{ old('contact_info') }}" placeholder="WA Panitia: 0812..." /></div>
        <x-ui.validation-summary />
        <div class="sm:col-span-2 flex justify-end gap-2">
            <x-ui.button variant="ghost" href="{{ route('admin.periods.index') }}">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Periode</x-ui.button>
        </div>
    </form>
</x-ui.card>
</x-admin.layouts.app>
