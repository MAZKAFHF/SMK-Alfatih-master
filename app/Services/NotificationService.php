<?php

namespace App\Services;

use App\Models\ApplicantNotification;
use App\Models\PPDBRegistration;

class NotificationService
{
    public static function notify(?int $accountId, ?PPDBRegistration $app, string $title, string $message = '', ?string $actionUrl = null): ?ApplicantNotification
    {
        if (! $accountId) {
            return null;
        }

        return ApplicantNotification::create([
            'account_id' => $accountId,
            'application_id' => $app?->id,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }
}
