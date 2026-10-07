<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbPeriod;
use App\Services\ApplicantAccountLifecycleService;
use App\Services\AuditService;
use App\Services\JakartaDateTime;
use App\Services\PpdbContext;
use App\Services\PpdbPeriodService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = PpdbPeriod::withCount('applications')->orderByDesc('id')->paginate(15);

        return view('admin.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.periods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'max:20', 'unique:ppdb_periods,academic_year'],
            'opens_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'closes_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:opens_at'],
            'announcement' => ['nullable', 'string', 'max:2000'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'contact_info' => ['nullable', 'string', 'max:500'],
        ]);
        try {
            $data['opens_at'] = JakartaDateTime::toStorage($data['opens_at'] ?? null, 'opens_at');
            $data['closes_at'] = JakartaDateTime::toStorage($data['closes_at'] ?? null, 'closes_at');
            $period = PpdbPeriodService::create($data, auth()->id());
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('admin.periods.index')->with('success', 'Periode '.$period->academic_year.' dibuat sebagai draf.');
    }

    public function edit(PpdbPeriod $period)
    {
        return view('admin.periods.edit', compact('period'));
    }

    public function update(Request $request, PpdbPeriod $period)
    {
        if (in_array($period->status, PpdbPeriod::CURRENT_STATUSES, true) && $request->input('academic_year') !== $period->academic_year) {
            return back()->withInput()->with('error', 'Tahun ajaran periode berjalan tidak dapat diubah.');
        }
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'max:20', 'unique:ppdb_periods,academic_year,'.$period->id],
            'opens_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'closes_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:opens_at'],
            'announcement' => ['nullable', 'string', 'max:2000'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'contact_info' => ['nullable', 'string', 'max:500'],
        ]);
        // Lifecycle akun pemohon SYSTEM-DRIVEN: hasil diumumkan dicatat saat
        // rilis, operasional selesai + batas retensi dihitung saat Tandai
        // Selesai. Tidak ada input tanggal lifecycle yang dapat diubah manual.
        $old = $period->toArray();
        $updates = [
            'academic_year' => $data['academic_year'],
            'opens_at' => JakartaDateTime::toStorage($data['opens_at'] ?? null, 'opens_at'),
            'closes_at' => JakartaDateTime::toStorage($data['closes_at'] ?? null, 'closes_at'),
            'announcement' => $data['announcement'] ?? null,
            'quota' => $data['quota'] ?? null,
            'contact_info' => $data['contact_info'] ?? null,
        ];
        $period->update($updates);
        PpdbContext::flush();
        AuditService::log('period_updated', $period, $old, $period->fresh()->toArray());

        return redirect()->route('admin.periods.index')->with('success', 'Periode diperbarui.');
    }

    public function open(Request $request, PpdbPeriod $period)
    {
        try {
            PpdbPeriodService::open($period, auth()->id());
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                throw $e;
            }

            return back()->withErrors($e->errors());
        }

        $message = 'Periode '.$period->academic_year.' dibuka.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('admin.periods.index'),
            ]);
        }

        return back()->with('success', $message);
    }

    public function close(Request $request, PpdbPeriod $period)
    {
        try {
            PpdbPeriodService::close($period, auth()->id(), $request->input('note'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Periode '.$period->academic_year.' ditutup. Data tersimpan sebagai riwayat.');
    }

    public function reopen(PpdbPeriod $period)
    {
        abort_unless(auth()->user()?->is_superadmin, 403, 'Hanya superadmin yang dapat membuka kembali periode.');
        try {
            PpdbPeriodService::reopen($period, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Periode '.$period->academic_year.' dibuka kembali.');
    }

    public function archive(PpdbPeriod $period)
    {
        try {
            PpdbPeriodService::archive($period, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Periode '.$period->academic_year.' diarsipkan.');
    }

    /**
     * Konfirmasi SELESAI: seluruh proses PPDB berakhir. Mengunci mutasi
     * operasional periode dan langsung memproses pembersihan akun pemohon
     * yang eligible (otomatis, tanpa hapus satu per satu).
     */
    public function complete(Request $request, PpdbPeriod $period)
    {
        abort_unless(auth()->user()?->is_superadmin, 403, 'Hanya superadmin yang dapat menandai periode selesai.');
        $request->validate(['confirm' => ['required', 'accepted']], [], ['confirm' => 'Konfirmasi penyelesaian']);
        try {
            $period = PpdbPeriodService::complete($period, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $summary = app(ApplicantAccountLifecycleService::class)
            ->cleanupPeriod($period->id, CarbonImmutable::now('UTC'));
        // Jejak completion: jika ada yang gagal, scheduler harian mengulang
        // otomatis karena akun gagal tetap eligible. Tidak ada status setengah.
        AuditService::system('applicant_cleanup_after_completion', $period, $summary);

        return back()->with('success', 'Periode '.$period->academic_year.' ditandai selesai. Pembersihan akun otomatis: '
            .$summary['retired'].' dipensiunkan, '.$summary['blocked'].' dilewati, '
            .$summary['failed'].' gagal dari '.$summary['checked'].' diperiksa.');
    }

    public function destroy(PpdbPeriod $period)
    {
        abort_unless(auth()->user()?->is_superadmin, 403);
        if ($period->applications()->exists()) {
            return back()->with('error', 'Periode berisi pendaftaran tidak dapat dihapus. Arsipkan saja.');
        }
        $label = $period->academic_year;
        $period->delete();
        PpdbContext::flush();
        AuditService::log('period_deleted', null, null, ['academic_year' => $label]);

        return redirect()->route('admin.periods.index')->with('success', "Periode {$label} dihapus.");
    }
}
