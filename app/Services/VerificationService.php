<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Models\PPDBRegistration;
use App\Models\StatusHistory;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    public static function verify(PPDBRegistration $app, int $actorId, bool $override = false, ?string $overrideReason = null): void
    {
        if ($app->period?->isLockedForOperations()) {
            throw ValidationException::withMessages(['status' => 'Periode PPDB sudah ditandai selesai. Verifikasi dikunci.']);
        }
        if (! in_array($app->application_status, [ApplicationStatus::Submitted, ApplicationStatus::Resubmitted], true)) {
            throw ValidationException::withMessages(['status' => 'Hanya aplikasi yang menunggu verifikasi yang dapat diverifikasi.']);
        }
        $invalid = $app->documents()
            ->whereIn('type', array_map(fn ($t) => $t->value, DocumentType::requiredInitially()))
            ->where('status', '!=', 'valid')
            ->count();
        if ($invalid > 0 && ! $override) {
            throw ValidationException::withMessages(['status' => "Masih ada {$invalid} dokumen wajib yang belum valid. Tandai per-dokumen dulu atau gunakan override eksplisit."]);
        }
        if ($invalid > 0 && $override && blank($overrideReason)) {
            throw ValidationException::withMessages(['status' => 'Override verifikasi wajib disertai alasan.']);
        }

        $from = $app->application_status->value;
        $app->update([
            'application_status' => ApplicationStatus::Verified,
            'status' => ApplicationStatus::Verified->toLegacy(),
            'verified_at' => now(),
        ]);
        StatusHistory::create([
            'application_id' => $app->id, 'from_status' => $from, 'to_status' => 'verified',
            'actor_id' => $actorId, 'note' => $override ? 'Override: '.$overrideReason : 'Terverifikasi',
        ]);
        NotificationService::notify($app->applicant_account_id, $app, 'Pendaftaran telah diverifikasi', 'Silakan pilih jadwal wawancara yang tersedia.', $app->applicant_account_id ? route('portal.applications.show', $app) : null);
    }

    public static function requestRevision(PPDBRegistration $app, int $actorId, string $note): void
    {
        if ($app->period?->isLockedForOperations()) {
            throw ValidationException::withMessages(['status' => 'Periode PPDB sudah ditandai selesai. Permintaan perbaikan dikunci.']);
        }
        if (! in_array($app->application_status, [ApplicationStatus::Submitted, ApplicationStatus::Resubmitted], true)) {
            throw ValidationException::withMessages(['status' => 'Hanya aplikasi menunggu verifikasi yang dapat diminta perbaikan.']);
        }
        if (blank($note)) {
            throw ValidationException::withMessages(['note' => 'Alasan perbaikan untuk pendaftar wajib diisi.']);
        }
        $from = $app->application_status->value;
        $app->update(['application_status' => ApplicationStatus::NeedsRevision, 'status' => 'pending', 'admin_notes' => $note]);
        StatusHistory::create([
            'application_id' => $app->id, 'from_status' => $from, 'to_status' => 'needs_revision',
            'actor_id' => $actorId, 'note' => $note,
        ]);
        NotificationService::notify($app->applicant_account_id, $app, 'Dokumen pendaftaran perlu diperbaiki', $note, $app->applicant_account_id ? route('portal.applications.show', $app) : null);
    }
}
