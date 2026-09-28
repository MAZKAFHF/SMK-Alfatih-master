<x-portal.layouts.app :title="'Ubah Draf — '.$application->name">
<h1 class="font-display text-xl font-extrabold">Ubah Draf — {{ $application->name }}</h1>
<p class="mt-1 text-sm text-slate-500">Tanda <span class="font-semibold text-red-500">*</span> wajib dilengkapi sebelum pendaftaran dikirim. Draf boleh disimpan sebagian.</p>
<form method="POST" novalidate action="{{ route('portal.applications.update', $application) }}" class="mt-6 space-y-6">@csrf @method('PUT')
<x-ui.card class="p-6"><h2 class="font-bold">Data Siswa & Alamat</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">
<div class="sm:col-span-2"><x-ui.input label="Nama Lengkap *" name="name" value="{{ old('name', $application->name) }}" required /></div>
<x-ui.input label="NIK *" name="nik" value="{{ old('nik', $application->nik) }}" inputmode="numeric" maxlength="16" help="16 digit." /><x-ui.input label="NISN *" name="nisn" value="{{ old('nisn', $application->nisn) }}" inputmode="numeric" maxlength="10" help="10 digit." />
<x-ui.select label="Jenis Kelamin *" name="gender" :value="old('gender', $application->gender)" :options="['laki-laki'=>'Laki-laki','perempuan'=>'Perempuan']" required />
<x-ui.input label="Tempat Lahir *" name="birth_place" value="{{ old('birth_place', $application->birth_place) }}" /><x-ui.date-picker label="Tanggal Lahir *" name="birth_date" value="{{ old('birth_date', $application->birth_date?->format('Y-m-d')) }}" min="2000-01-01" :max="now('Asia/Jakarta')->toDateString()" :min-year="2000" :max-year="now('Asia/Jakarta')->year" />
<x-ui.input label="No. HP Siswa" name="phone" value="{{ old('phone', $application->phone) }}" help="Opsional." /><x-ui.input label="Email Siswa" name="email" type="email" value="{{ old('email', $application->email) }}" help="Opsional." />
<div class="sm:col-span-2"><x-ui.textarea label="Alamat Lengkap *" name="address" rows="2">{{ old('address', $application->address) }}</x-ui.textarea></div>
<x-ui.input label="Provinsi *" name="province" value="{{ old('province', $application->province) }}" /><x-ui.input label="Kabupaten/Kota *" name="city" value="{{ old('city', $application->city) }}" />
<x-ui.input label="Kecamatan *" name="district" value="{{ old('district', $application->district) }}" /><x-ui.input label="Kelurahan/Desa *" name="village" value="{{ old('village', $application->village) }}" />
<x-ui.input label="Kode Pos *" name="postal_code" value="{{ old('postal_code', $application->postal_code) }}" />
<x-ui.input label="Asal Sekolah *" name="school_origin" value="{{ old('school_origin', $application->school_origin) }}" />
</div></x-ui.card>
<x-ui.card class="p-6"><h2 class="font-bold">Orang Tua / Wali</h2><p class="mt-1 text-xs text-slate-500">Nama ayah & ibu wajib. Isi minimal satu nomor kontak orang tua/wali. Wali hanya bila dipakai.</p><div class="mt-4 grid gap-4 sm:grid-cols-2">
<x-ui.input label="Nama Ayah *" name="father_name" value="{{ old('father_name', $application->father_name) }}" /><x-ui.input label="HP Ayah" name="father_phone" value="{{ old('father_phone', $application->father_phone) }}" />
<x-ui.input label="Pekerjaan Ayah" name="father_occupation" value="{{ old('father_occupation', $application->father_occupation) }}" help="Opsional." />
<x-ui.input label="Nama Ibu *" name="mother_name" value="{{ old('mother_name', $application->mother_name) }}" /><x-ui.input label="HP Ibu" name="mother_phone" value="{{ old('mother_phone', $application->mother_phone) }}" />
<x-ui.input label="Pekerjaan Ibu" name="mother_occupation" value="{{ old('mother_occupation', $application->mother_occupation) }}" help="Opsional." />
<x-ui.input label="Nama Wali" name="guardian_name" value="{{ old('guardian_name', $application->guardian_name) }}" help="Bila diisi, HP & hubungan wali ikut wajib." /><x-ui.input label="HP Wali" name="guardian_phone" value="{{ old('guardian_phone', $application->guardian_phone) }}" />
<x-ui.input label="Hubungan Wali" name="guardian_relation" value="{{ old('guardian_relation', $application->guardian_relation) }}" />
<div class="sm:col-span-2"><x-ui.select label="Program Keahlian (satu saja) *" name="program_id" :value="old('program_id', $application->program_id)" :options="$programs->pluck('name','id')->all()" required /></div>
</div></x-ui.card>
<x-ui.validation-summary />
<div class="flex justify-end gap-2"><x-ui.button variant="ghost" href="{{ route('portal.applications.show', $application) }}">Batal</x-ui.button><x-ui.button type="submit">Simpan Draf</x-ui.button></div>
</form>
</x-portal.layouts.app>
