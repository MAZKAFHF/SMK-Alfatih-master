<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewAppointmentStatus;
use App\Models\PPDBRegistration;

/**
 * SATU-SATUNYA sumber kebenaran untuk "wawancara selesai".
 *
 * Aturan kanonis:
 *  - appointment.status == attended
 *  - DAN assessment.attendance == attended
 *  - ATAU (kompatibilitas historis) application_status sudah melewati
 *    tahap wawancara (waiting_decision / interviewed / passed / not_passed)
 *    dengan bukti pendukung (decision ada / attended_at ada / history).
 *
 * Semua pemanggil (detail admin, halaman interview, form keputusan,
 * workflow hasil, dashboard, next-action resolver, tests) WAJIB memakai
 * service ini, bukan menduplikasi pengecekan status mentah.
 */
class InterviewCompletion
{
    /**
     * Apakah wawancara aplikasi ini sudah selesai secara permanen?
     */
    public static function isCompleted(PPDBRegistration $app): bool
    {
        $app->loadMissing(['appointment.assessment', 'decision']);

        $appt = $app->appointment;

        // Bukti primer yang durable: appointment + assessment.
        if ($appt) {
            $status = $appt->status instanceof InterviewAppointmentStatus
                ? $appt->status
                : InterviewAppointmentStatus::tryFrom((string) $appt->status);

            $attendance = $appt->assessment?->attendance;

            if ($status === InterviewAppointmentStatus::Attended && $attendance === 'attended') {
                return true;
            }

            // attended_at adalah bukti kehadiran yang bertahan walau enum berubah.
            if ($appt->attended_at && $attendance === 'attended') {
                return true;
            }
        }

        // Kompatibilitas historis:
        // - complete() satu-satunya penulis waiting_decision, jadi status itu
        //   sendiri adalah bukti.
        // - passed / not_passed hanya bisa dicapai lewat decide() yang dulu
        //   mensyaratkan waiting_decision/interviewed, jadi decision yang ada
        //   membuktikan wawancara pernah selesai.
        $status = $app->application_status;

        if ($status === ApplicationStatus::WaitingDecision || $status === ApplicationStatus::Interviewed) {
            return true;
        }

        if ($status === ApplicationStatus::Passed || $status === ApplicationStatus::NotPassed) {
            // Jika decision ada, itu kontradiksi internal bila kita klaim belum selesai.
            // Perlakukan sebagai selesai agar admin bisa mengedit keputusan.
            if ($app->decision) {
                return true;
            }
            // Fallback: appointment attended walau decision terhapus manual.
            if ($appt && $appt->attended_at) {
                return true;
            }
        }

        return false;
    }

    /**
     * Label tunggal untuk UI admin. Jangan tampilkan kombinasi kontradiktif
     * (mis. "Lulus" + "Wawancara belum selesai") kecuali data rusak.
     *
     * @return array{completed:bool,label:string,inconsistent:bool}
     */
    public static function statusForAdmin(PPDBRegistration $app): array
    {
        $completed = self::isCompleted($app);
        $hasDecision = (bool) $app->decision;

        $inconsistent = $hasDecision && ! $completed;

        return [
            'completed' => $completed,
            'label' => $completed ? 'Wawancara selesai' : 'Wawancara belum selesai',
            'inconsistent' => $inconsistent,
        ];
    }

    /**
     * Alasan manusia untuk debugging / audit. Tidak untuk user-facing guard.
     */
    public static function reason(PPDBRegistration $app): string
    {
        $app->loadMissing(['appointment.assessment', 'decision']);

        if (self::isCompleted($app)) {
            if ($app->appointment && $app->appointment->attended_at) {
                return 'appointment attended + assessment tersimpan';
            }
            if ($app->decision) {
                return 'keputusan internal sudah ada (bukti historis)';
            }

            return 'application_status sudah melewati wawancara ('.$app->application_status->value.')';
        }

        if (! $app->appointment) {
            return 'belum ada appointment wawancara';
        }

        $st = $app->appointment->status instanceof InterviewAppointmentStatus
            ? $app->appointment->status->value
            : (string) $app->appointment->status;

        if ($st !== 'attended') {
            return 'appointment status='.$st.' (bukan attended)';
        }

        if (! $app->appointment->assessment || ($app->appointment->assessment->attendance ?? null) !== 'attended') {
            return 'assessment belum menyimpan attendance=attended';
        }

        return 'application_status='.$app->application_status->value;
    }
}
