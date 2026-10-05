<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Models\PPDBRegistration;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, PPDBRegistration $app): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return (int) $app->applicant_account_id === (int) $user->id;
    }

    public function update(User $user, PPDBRegistration $app): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return (int) $app->applicant_account_id === (int) $user->id && ! $app->isLockedForApplicant();
    }

    public function uploadDocument(User $user, PPDBRegistration $app): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ((int) $app->applicant_account_id !== (int) $user->id) {
            return false;
        }

        if (in_array($app->application_status, [ApplicationStatus::Draft, ApplicationStatus::NeedsRevision], true)) {
            return true;
        }

        // Admin dapat meminta koreksi per dokumen ketika aplikasi masih berstatus
        // submitted/resubmitted. Dalam keadaan itu hanya dokumen yang ditandai
        // needs_revision yang dibuka; controller memeriksa jenisnya kembali.
        return $app->relationLoaded('documents')
            ? $app->documents->contains(fn ($document) => $document->status === DocumentStatus::NeedsRevision)
            : $app->documents()->where('status', DocumentStatus::NeedsRevision->value)->exists();
    }
}
