<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistrationStatusRequest;
use App\Models\PPDBRegistration;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $registrations = PPDBRegistration::query()
            ->with('program')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations.index', compact('registrations'));
    }

    public function show(PPDBRegistration $registration)
    {
        $registration->load('program');

        return view('admin.registrations.show', compact('registration'));
    }

    public function update(UpdateRegistrationStatusRequest $request, PPDBRegistration $registration)
    {
        $old = $registration->getOriginal();
        try {
            $validated = $request->validated();
            if (isset($validated['admin_notes'])) {
                $validated['admin_notes'] = trim((string) $validated['admin_notes']);
            }
            $registration->update($validated);

            AuditService::log('ppdb_status_update', $registration, ['status' => $old['status']], ['status' => $registration->status->value, 'admin_notes' => $registration->admin_notes]);

            return back()->with('success', "Status pendaftaran {$registration->registration_number} berhasil diperbarui.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui status pendaftaran. Silakan coba lagi.');
        }
    }

    // Soft-delete all — superadmin + password + confirmation
    public function destroyAll(Request $request)
    {
        if (! auth()->user()?->is_superadmin) {
            abort(403, 'Hanya superadmin dapat menghapus massal.');
        }

        $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'in:HAPUS SEMUA'],
        ]);

        if (! Hash::check($request->input('password'), auth()->user()->password)) {
            return back()->with('error', 'Password salah — penghapusan dibatalkan.');
        }

        try {
            $count = PPDBRegistration::count();

            if ($count === 0) {
                return back()->with('warning', 'Tidak ada data pendaftaran yang dapat dihapus.');
            }

            PPDBRegistration::query()->delete(); // soft

            AuditService::log('ppdb_destroy_all', null, null, ['count' => $count]);

            return redirect()->route('admin.registrations.index')
                ->with('success', "{$count} data pendaftaran dipindahkan ke Trash (soft delete).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus data pendaftaran. Silakan coba lagi.');
        }
    }

    public function destroy(PPDBRegistration $registration)
    {
        try {
            $registration->delete();
            AuditService::log('ppdb_delete', $registration);

            return redirect()->route('admin.registrations.index')
                ->with('success', 'Data pendaftaran dipindahkan ke Trash.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus data pendaftaran. Silakan coba lagi.');
        }
    }

    public function trash(Request $request)
    {
        $registrations = PPDBRegistration::onlyTrashed()
            ->with('program')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($qq) => $qq->where('name', 'like', "%{$search}%")->orWhere('registration_number', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations.trash', compact('registrations'));
    }

    public function restore(int $id)
    {
        $registration = PPDBRegistration::onlyTrashed()->findOrFail($id);
        $registration->restore();
        AuditService::log('ppdb_restore', $registration);

        return back()->with('success', "Pendaftaran {$registration->registration_number} dipulihkan.");
    }

    public function forceDelete(int $id)
    {
        if (! auth()->user()?->is_superadmin) {
            abort(403);
        }
        $registration = PPDBRegistration::onlyTrashed()->findOrFail($id);
        $label = $registration->registration_number;
        $registration->forceDelete();
        AuditService::log('ppdb_force_delete', null, null, ['registration_number' => $label]);

        return back()->with('success', "Pendaftaran {$label} dihapus permanen.");
    }

    public function export(Request $request)
    {
        $query = PPDBRegistration::query()->with('program')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->integer('program_id')))
            ->when($request->filled('academic_year'), fn ($q) => $q->where('academic_year', $request->string('academic_year')))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$request->string('search')}%")->orWhere('registration_number', 'like', "%{$request->string('search')}%")))
            ->orderBy('created_at');

        $filename = 'ppdb-'.now()->format('Ymd_His').'.csv';

        AuditService::log('ppdb_export', null, null, ['filters' => $request->query(), 'count' => $query->count()]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['Nomor Registrasi', 'Nama', 'NISN', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Asal Sekolah', 'HP', 'Email', 'Orang Tua', 'Program', 'Status', 'Tahun Ajaran', 'Tanggal Daftar'];

        return response()->stream(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $columns);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->registration_number,
                        $r->name,
                        $r->nisn,
                        $r->gender,
                        $r->birth_place,
                        $r->birth_date?->format('Y-m-d'),
                        $r->address,
                        $r->school_origin,
                        $r->phone,
                        $r->email,
                        $r->parent_name,
                        $r->program?->name,
                        $r->status->value,
                        $r->academic_year,
                        $r->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }
}
