<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Sengaja menerima string mentah agar semua percobaan autentikasi,
            // termasuk payload otomatis/berbahaya, dapat dicatat dengan aman.
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}
