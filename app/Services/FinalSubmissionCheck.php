<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\PPDBRegistration;
use App\Models\Program;
use Illuminate\Support\Facades\Validator;

/**
 * Kontrak field PPDB: DRAFT boleh tidak lengkap (kolom DB nullable),
 * FINAL SUBMISSION wajib lengkap dan divalidasi server-side di sini.
 */
class FinalSubmissionCheck
{
    /** Aturan wajib FINAL (draft tetap longgar di controller store/update). */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'digits:16'],
            'nisn' => ['required', 'digits:10'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'school_origin' => ['required', 'string', 'max:150'],
            'father_name' => ['required', 'string', 'max:150'],
            'father_phone' => ['nullable', 'string', 'max:30'],
            'mother_name' => ['required', 'string', 'max:150'],
            'mother_phone' => ['nullable', 'string', 'max:30'],
            // Wali kondisional: jika salah satu diisi, ketiganya wajib.
            'guardian_name' => ['nullable', 'string', 'max:150', 'required_with:guardian_phone,guardian_relation'],
            'guardian_phone' => ['nullable', 'string', 'max:30', 'required_with:guardian_name,guardian_relation'],
            'guardian_relation' => ['nullable', 'string', 'max:50', 'required_with:guardian_name,guardian_phone'],
            // Tepat SATU program: `integer` menolak array multi-program (exists lolos untuk array).
            'program_id' => ['required', 'integer', 'exists:programs,id'],
        ];
    }

    /** Field => label seksi untuk checklist "bagian belum lengkap". */
    public static function sections(): array
    {
        return [
            'Data Siswa' => ['name', 'nik', 'nisn', 'birth_place', 'birth_date', 'gender'],
            'Alamat' => ['address', 'province', 'city', 'district', 'village', 'postal_code'],
            'Orang Tua' => ['father_name', 'father_phone', 'mother_name', 'mother_phone', 'parent_contact'],
            'Wali' => ['guardian_name', 'guardian_phone', 'guardian_relation'],
            'Asal Sekolah' => ['school_origin'],
            'Program' => ['program_id'],
            'Dokumen' => ['dokumen_kk', 'dokumen_ktp_ortu', 'dokumen_akta', 'dokumen_rapor', 'dokumen_foto'],
        ];
    }

    /** Nama manusiawi yang ditampilkan di checklist kelengkapan pemohon. */
    public static function labels(): array
    {
        return [
            'name' => 'Nama lengkap',
            'nik' => 'NIK (16 digit)',
            'nisn' => 'NISN (10 digit)',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal lahir',
            'gender' => 'Jenis kelamin',
            'address' => 'Alamat lengkap',
            'province' => 'Provinsi',
            'city' => 'Kabupaten/Kota',
            'district' => 'Kecamatan',
            'village' => 'Kelurahan/Desa',
            'postal_code' => 'Kode pos',
            'school_origin' => 'Asal sekolah',
            'father_name' => 'Nama ayah',
            'father_phone' => 'Nomor kontak orang tua/wali',
            'mother_name' => 'Nama ibu',
            'mother_phone' => 'Nomor HP ibu',
            'guardian_name' => 'Nama wali',
            'guardian_phone' => 'Nomor HP wali',
            'guardian_relation' => 'Hubungan wali',
            'program_id' => 'Program keahlian',
            'dokumen_kk' => 'Kartu Keluarga (KK)',
            'dokumen_ktp_ortu' => 'KTP orang tua/wali',
            'dokumen_akta' => 'Akta kelahiran',
            'dokumen_rapor' => 'Rapor',
            'dokumen_foto' => 'Foto siswa',
        ];
    }

    /**
     * @return array{valid: bool, errors: \Illuminate\Support\MessageBag, missing_sections: array<string, string[]>}
     */
    public static function check(PPDBRegistration $app): array
    {
        DocumentService::ensurePlaceholders($app);
        $app->refresh();

        $data = $app->only(array_keys(static::rules()));
        // Normalisasi kosong -> null agar `required` bekerja konsisten.
        $data = array_map(fn ($v) => is_string($v) && trim($v) === '' ? null : $v, $data);

        $validator = Validator::make($data, static::rules());

        // Program harus aktif (bukan sekadar ada).
        $validator->after(function ($v) use ($data) {
            if (! empty($data['program_id']) && ! Program::whereKey($data['program_id'])->where('status', 'active')->exists()) {
                $v->errors()->add('program_id', 'Program keahlian yang dipilih sudah tidak aktif. Silakan pilih program lain.');
            }
            // Minimal satu kontak orang tua/wali.
            if (empty($data['father_phone']) && empty($data['mother_phone']) && empty($app->guardian_phone)) {
                $v->errors()->add('father_phone', 'Isi minimal satu nomor kontak orang tua atau wali.');
            }
        });

        // Dokumen wajib awal (ijazah tahap lanjut, tidak digate di sini).
        $uploaded = $app->documents()->whereNotNull('path')->pluck('type')->map(fn ($t) => $t->value)->all();
        $docLabels = ['kk' => 'Kartu Keluarga (KK)', 'ktp_ortu' => 'KTP orang tua/wali', 'akta' => 'Akta Kelahiran', 'rapor' => 'Rapor', 'foto' => 'Foto siswa'];
        $docErrors = [];
        foreach (array_keys($docLabels) as $type) {
            if (! in_array($type, $uploaded, true)) {
                $docErrors['dokumen_'.$type] = 'Unggah '.$docLabels[$type].'.';
            }
        }

        $failed = $validator->fails() || $docErrors !== [];
        $errors = $validator->errors();
        foreach ($docErrors as $key => $msg) {
            $errors->add($key, $msg);
        }

        // Petakan field gagal -> seksi.
        $failedKeys = array_unique(array_merge($validator->errors()->keys(), array_keys($docErrors)));
        $missingSections = [];
        foreach (static::sections() as $section => $fields) {
            $hit = array_values(array_intersect($fields, $failedKeys));
            if ($hit !== []) {
                $missingSections[$section] = $hit;
            }
        }

        return ['valid' => ! $failed, 'errors' => $errors, 'missing_sections' => $missingSections];
    }
}
