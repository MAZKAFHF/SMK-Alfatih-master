<x-admin.layouts.app :title="'Entri Manual PPDB'">
<x-admin.page-head title="Entri Manual" context="Untuk kasus pengecualian — boleh saat publik ditutup. Tercatat sebagai admin_manual." />
<x-ui.card class="p-6">
<form method="POST" novalidate action="{{ route('admin.registrations.store') }}" class="grid gap-4 sm:grid-cols-2">@csrf
<div class="sm:col-span-2"><x-ui.input label="Nama Lengkap *" name="name" value="{{ old('name') }}" required /></div>
<x-ui.select label="Jenis Kelamin *" name="gender" :value="old('gender')" :options="['laki-laki'=>'Laki-laki','perempuan'=>'Perempuan']" required />
<x-ui.select label="Program *" name="program_id" :value="old('program_id')" :options="$programs->pluck('name','id')->all()" required />
<x-ui.select label="Periode *" name="period_id" :value="old('period_id')" :options="$periods->pluck('academic_year','id')->all()" required />
<x-ui.input label="NISN" name="nisn" value="{{ old('nisn') }}" /><x-ui.input label="HP" name="phone" value="{{ old('phone') }}" />
<x-ui.input label="Asal Sekolah" name="school_origin" value="{{ old('school_origin') }}" /><x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" />
<div class="sm:col-span-2"><x-ui.input label="Alasan override (wajib bila periode tutup/penuh)" name="override_reason" value="{{ old('override_reason') }}" placeholder="Contoh: pendaftar datang langsung ke sekolah" /></div>
<div class="sm:col-span-2 flex justify-end gap-2"><x-ui.button variant="ghost" href="{{ route('admin.registrations.index') }}">Batal</x-ui.button><x-ui.button type="submit">Simpan Manual</x-ui.button></div>
</form>
</x-ui.card>
</x-admin.layouts.app>
