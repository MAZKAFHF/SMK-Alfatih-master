<x-portal.layouts.app :title="'Tambah Calon Siswa'">
<h1 class="font-display text-xl font-extrabold">Tambah Calon Siswa</h1>
<p class="text-sm text-slate-500">Periode {{ $period?->academic_year }} — satu program per anak. Tanda <span class="font-semibold text-red-500">*</span> wajib dilengkapi sebelum pendaftaran dikirim (boleh disimpan sebagai draf dulu).</p>
<form method="POST" novalidate action="{{ route('portal.applications.store') }}" class="mt-6 space-y-6">@csrf
<x-ui.card class="p-6"><h2 class="font-bold">1 — Data Siswa</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
<div class="sm:col-span-2"><x-ui.input label="Nama Lengkap *" name="name" value="{{ old('name') }}" required help="Sesuai akta kelahiran." /></div>
<x-ui.input label="NIK *" name="nik" value="{{ old('nik') }}" help="16 digit sesuai KK. Wajib sebelum dikirim." inputmode="numeric" maxlength="16" /><x-ui.input label="NISN *" name="nisn" value="{{ old('nisn') }}" help="10 digit. Wajib sebelum dikirim." inputmode="numeric" maxlength="10" />
<x-ui.input label="Tempat Lahir *" name="birth_place" value="{{ old('birth_place') }}" /><x-ui.date-picker label="Tanggal Lahir *" name="birth_date" value="{{ old('birth_date') }}" min="2000-01-01" :max="now('Asia/Jakarta')->toDateString()" :min-year="2000" :max-year="now('Asia/Jakarta')->year" />
<x-ui.select label="Jenis Kelamin *" name="gender" id="gender" :value="old('gender')" :options="['laki-laki'=>'Laki-laki','perempuan'=>'Perempuan']" required />
<x-ui.input label="No. HP Siswa" name="phone" value="{{ old('phone') }}" help="Opsional — boleh dikosongkan bila belum punya HP." /><x-ui.input label="Email Siswa" name="email" type="email" value="{{ old('email') }}" help="Opsional." />
<div class="sm:col-span-2"><x-ui.textarea label="Alamat Lengkap *" name="address" rows="2">{{ old('address') }}</x-ui.textarea></div>
<x-ui.input label="Provinsi *" name="province" value="{{ old('province') }}" /><x-ui.input label="Kabupaten/Kota *" name="city" value="{{ old('city') }}" />
<x-ui.input label="Kecamatan *" name="district" value="{{ old('district') }}" /><x-ui.input label="Kelurahan/Desa *" name="village" value="{{ old('village') }}" />
<x-ui.input label="Kode Pos *" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" />
</div></x-ui.card>
<x-ui.card class="p-6"><h2 class="font-bold">2 — Orang Tua / Wali</h2><p class="mt-1 text-xs text-slate-500">Nama ayah & ibu wajib. Isi minimal satu nomor kontak orang tua/wali.</p><div class="mt-4 grid gap-4 sm:grid-cols-2">
<x-ui.input label="Nama Ayah *" name="father_name" value="{{ old('father_name') }}" /><x-ui.input label="HP Ayah" name="father_phone" value="{{ old('father_phone') }}" help="Salah satu kontak orang tua/wali wajib diisi." />
<x-ui.input label="Pekerjaan Ayah" name="father_occupation" value="{{ old('father_occupation') }}" help="Opsional." />
<x-ui.input label="Nama Ibu *" name="mother_name" value="{{ old('mother_name') }}" /><x-ui.input label="HP Ibu" name="mother_phone" value="{{ old('mother_phone') }}" help="Salah satu kontak orang tua/wali wajib diisi." />
<x-ui.input label="Pekerjaan Ibu" name="mother_occupation" value="{{ old('mother_occupation') }}" help="Opsional." />
</div>
<h3 class="mt-5 text-sm font-bold">Wali (hanya bila memakai wali)</h3>
<div class="mt-3 grid gap-4 sm:grid-cols-2">
<x-ui.input label="Nama Wali" name="guardian_name" value="{{ old('guardian_name') }}" help="Bila diisi, HP & hubungan wali ikut wajib." /><x-ui.input label="HP Wali" name="guardian_phone" value="{{ old('guardian_phone') }}" />
<x-ui.input label="Hubungan Wali" name="guardian_relation" value="{{ old('guardian_relation') }}" placeholder="Contoh: Paman" />
</div></x-ui.card>
<x-ui.card class="p-6"><h2 class="font-bold">3 — Asal Sekolah & Program</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
<x-ui.input label="Asal Sekolah *" name="school_origin" value="{{ old('school_origin') }}" placeholder="Contoh: SMPN 1 Kota Bogor" /><x-ui.input label="Tahun Lulus" name="graduation_year" value="{{ old('graduation_year') }}" help="Opsional." />
<div class="sm:col-span-2"><x-ui.select label="Program Keahlian (satu saja) *" name="program_id" id="program_id" :value="old('program_id')" :options="$programs->pluck('name','id')->all()" placeholder="Pilih program" required /></div>
</div></x-ui.card>
<x-ui.validation-summary />
<div class="flex justify-end gap-2"><x-ui.button variant="ghost" href="{{ route('portal.dashboard') }}">Batal</x-ui.button><x-ui.button type="submit">Simpan Draf</x-ui.button></div>
</form>
</x-portal.layouts.app>
