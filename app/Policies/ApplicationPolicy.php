<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
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

        return (int) $app->applicant_account_id === (int) $user->id
            && in_array($app->application_status, [ApplicationStatus::Draft, ApplicationStatus::NeedsRevision], true);
    }
}
