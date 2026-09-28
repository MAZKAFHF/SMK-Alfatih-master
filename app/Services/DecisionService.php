<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DecisionResult;
use App\Models\ApplicationDecision;
use App\Models\PPDBRegistration;
use App\Models\StatusHistory;
use Illuminate\Validation\ValidationException;

class DecisionService
{
    public static function decide(PPDBRegistration $app, DecisionResult $result, int $actorId, ?string $internalNote, ?string $applicantMessage): ApplicationDecision
    {
        if ($app->period?->isLockedForOperations()) {
            throw ValidationException::withMessages(['status' => 'Periode PPDB sudah ditandai selesai. Keputusan dikunci.']);
        }
        // SATU aturan kanonis: wawancara selesai = InterviewCompletion.
        // Ini mencakup appointment+assessment yang durable DAN status historis
        // (waiting_decision/interviewed/passed+decision) agar edit keputusan
        // setelah reload / setelah keputusan pertama tetap lolos tanpa re-save.
        if (! InterviewCompletion::isCompleted($app)) {
            throw ValidationException::withMessages(['status' => 'Keputusan hanya dapat ditetapkan setelah wawancara selesai.']);
        }
        // Tolak status yang jelas belum sampai tahap keputusan.
        // Izinkan: waiting_decision, interviewed (keputusan pertama) +
        // passed, not_passed (edit keputusan yang sudah ada).
        if (! in_array($app->application_status, [
            ApplicationStatus::WaitingDecision,
            ApplicationStatus::Interviewed,
            ApplicationStatus::Passed,
            ApplicationStatus::NotPassed,
        ], true)) {
            throw ValidationException::withMessages(['status' => 'Keputusan hanya dapat ditetapkan setelah wawancara selesai.']);
        }
        $existing = ApplicationDecision::where('application_id', $app->id)->first();
        $wasReleased = (bool) $existing?->released_at;
        $resultChanged = $existing && $existing->result->value !== $result->value;

        $decision = ApplicationDecision::updateOrCreate(
            ['application_id' => $app->id],
            [
                'result' => $result->value, 'decided_by' => $actorId, 'decided_at' => now(),
                'internal_note' => $internalNote, 'applicant_message' => $applicantMessage,
                // Jika hasil berubah setelah dirilis, kembalikan ke belum-dirilis
                // agar admin wajib rilis ulang — jangan diam-diam mengubah
                // hasil publik yang sudah dilihat pendaftar.
                ...(($wasReleased && $resultChanged) ? ['released_at' => null] : []),
            ]
        );
        $from = $app->application_status->value;
        $to = $result === DecisionResult::Passed ? ApplicationStatus::Passed : ApplicationStatus::NotPassed;
        $app->update(['application_status' => $to, 'status' => $to->toLegacy()]);
        $note = 'Keputusan internal: '.$result->label();
        if ($wasReleased && $resultChanged) {
            $note .= ' (hasil berubah setelah rilis — perlu rilis ulang)';
        } elseif ($existing) {
            $note .= ' (edit keputusan)';
        }
        StatusHistory::create([
            'application_id' => $app->id, 'from_status' => $from, 'to_status' => $to->value,
            'actor_id' => $actorId, 'note' => $note,
        ]);

        return $decision->fresh();
    }

    public static function release(ApplicationDecision $decision, int $actorId): void
    {
        $decision->update(['released_at' => $decision->released_at ?? now()]);
        $app = $decision->application;
        // SYSTEM-DRIVEN lifecycle: pengumuman hasil pertama kali dicatat di
        // level periode. Admin tidak pernah mengisi tanggal ini manual.
        $period = $app->period;
        if ($period && ! $period->results_released_at) {
            $period->update(['results_released_at' => now()]);
        }
        NotificationService::notify($app->applicant_account_id, $app, 'Hasil PPDB telah tersedia', 'Silakan lihat hasil seleksi Anda.', $app->applicant_account_id ? route('portal.applications.show', $app) : null);
        StatusHistory::create([
            'application_id' => $app->id, 'from_status' => $app->application_status->value,
            'to_status' => $app->application_status->value, 'actor_id' => $actorId, 'note' => 'Hasil dirilis ke pendaftar',
        ]);
    }
}
