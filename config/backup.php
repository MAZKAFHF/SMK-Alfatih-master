<?php

return [
    'enabled' => env('APP_BACKUP_ENABLED', true),
    'directory' => env('APP_BACKUP_DIR') ?: storage_path('app/backups'),
    'retention' => max(1, (int) env('APP_BACKUP_RETENTION', 14)),
    'daily_at' => env('APP_BACKUP_DAILY_AT', '02:00'),
];
