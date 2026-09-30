<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminCodeController extends Controller
{
    public function create(Request $request)
    {
        if (filled($request->user()->admin_code)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.setup-code')->with('title', 'Buat Kode Admin');
    }

    public function store(Request $request)
    {
        if (filled($request->user()->admin_code)) {
            return redirect()->route('admin.dashboard');
        }

        $data = $request->validate([
            'admin_code' => ['required', 'digits:4', 'confirmed'],
        ], [], [
            'admin_code' => 'Kode admin',
            'admin_code_confirmation' => 'Konfirmasi kode admin',
        ]);

        $request->user()->forceFill([
            'admin_code' => $data['admin_code'],
            'admin_code_set_at' => now(),
        ])->save();

        AuditService::log('admin_code_created', $request->user());

        return redirect()->route('admin.dashboard')->with('success', 'Kode admin berhasil dibuat. Simpan kode ini dengan aman.');
    }
}
