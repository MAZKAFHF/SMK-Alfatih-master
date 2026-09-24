<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePPDBRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'address' => ['nullable', 'string', 'max:500'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'program_id' => ['required', 'exists:programs,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'nisn' => 'NISN',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'gender' => 'Jenis Kelamin',
            'address' => 'Alamat',
            'school_origin' => 'Asal Sekolah',
            'phone' => 'No. HP / WhatsApp',
            'email' => 'Email',
            'parent_name' => 'Nama Orang Tua / Wali',
            'program_id' => 'Program Keahlian',
        ];
    }
}
