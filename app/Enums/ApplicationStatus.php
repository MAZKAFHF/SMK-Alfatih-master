<?php

namespace App\Enums;

/**
 * State machine kanonis untuk seluruh alur aplikasi PPDB.
 */
enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case NeedsRevision = 'needs_revision';
    case Resubmitted = 'resubmitted';
    case Verified = 'verified';
    case WaitingSlot = 'waiting_slot';
    case Scheduled = 'scheduled';
    case Interviewed = 'interviewed';
    case WaitingDecision = 'waiting_decision';
    case Passed = 'passed';
    case NotPassed = 'not_passed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Menunggu Verifikasi',
            self::NeedsRevision => 'Perlu Perbaikan',
            self::Resubmitted => 'Menunggu Verifikasi Ulang',
            self::Verified => 'Terverifikasi',
            self::WaitingSlot => 'Menunggu Pemilihan Jadwal',
            self::Scheduled => 'Wawancara Terjadwal',
            self::Interviewed => 'Menunggu Hasil',
            self::WaitingDecision => 'Menunggu Hasil',
            self::Passed => 'Lulus',
            self::NotPassed => 'Belum Lulus',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Submitted, self::Resubmitted => 'amber',
            self::NeedsRevision => 'orange',
            self::Verified => 'green',
            self::WaitingSlot, self::Scheduled => 'sky',
            self::Interviewed, self::WaitingDecision => 'violet',
            self::Passed => 'emerald',
            self::NotPassed => 'red',
            self::Cancelled => 'slate',
        };
    }

    /** @return ApplicationStatus[] */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::NeedsRevision, self::Verified, self::Cancelled],
            self::NeedsRevision => [self::Resubmitted, self::Cancelled],
            self::Resubmitted => [self::NeedsRevision, self::Verified, self::Cancelled],
            self::Verified => [self::WaitingSlot, self::Cancelled],
            self::WaitingSlot => [self::Scheduled, self::Cancelled],
            self::Scheduled => [self::Interviewed, self::Cancelled],
            self::Interviewed => [self::WaitingDecision],
            self::WaitingDecision => [self::Passed, self::NotPassed],
            // Edit keputusan internal diizinkan tanpa re-save wawancara.
            // Guard tetap memakai InterviewCompletion::isCompleted().
            self::Passed => [self::NotPassed, self::Passed],
            self::NotPassed => [self::Passed, self::NotPassed],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public static function fromLegacy(string $legacy): self
    {
        return match ($legacy) {
            'pending' => self::Submitted,
            'accepted' => self::Passed,
            'rejected' => self::NotPassed,
            'cancelled' => self::Cancelled,
            default => self::Draft,
        };
    }

    public function toLegacy(): string
    {
        return match ($this) {
            self::Passed => 'accepted',
            self::NotPassed => 'rejected',
            self::Cancelled => 'cancelled',
            default => 'pending',
        };
    }
}
