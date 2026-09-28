<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(string $action, ?Model $model = null, ?array $old = null, ?array $new = null): AuditLog
    {
        return AuditLog::record(Auth::user(), $action, $model, $old, $new);
    }

    public static function system(string $action, ?Model $model = null, ?array $metadata = null): AuditLog
    {
        return AuditLog::recordSystem($action, $model, $metadata);
    }
}
