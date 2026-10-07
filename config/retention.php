<?php

return [
    'logs' => [
        'enabled' => (bool) env('LOG_RETENTION_ENABLED', true),
        'days' => max(1, (int) env('DATABASE_LOG_RETENTION_DAYS', 3)),
        'batch_size' => max(100, (int) env('LOG_PRUNE_BATCH_SIZE', 1000)),
        'daily_at' => env('LOG_PRUNE_DAILY_AT', '03:00'),
    ],

    'trash' => [
        'enabled' => (bool) env('TRASH_PURGE_ENABLED', true),
        'days' => max(1, (int) env('TRASH_RETENTION_DAYS', 7)),
        'batch_size' => max(10, (int) env('TRASH_PURGE_BATCH_SIZE', 100)),
        'daily_at' => env('TRASH_PURGE_DAILY_AT', '03:15'),
        'include_contact_messages' => (bool) env('TRASH_PURGE_CONTACT_MESSAGES', true),
    ],

    'applicants' => [
        'enabled' => (bool) env('APPLICANT_CLEANUP_ENABLED', true),
        'orphan_enabled' => (bool) env('APPLICANT_ORPHAN_CLEANUP_ENABLED', true),
        'orphan_days' => max(1, (int) env('APPLICANT_ORPHAN_RETENTION_DAYS', 3)),
        'verified_unused_enabled' => (bool) env('APPLICANT_VERIFIED_UNUSED_CLEANUP_ENABLED', true),
        'verified_unused_days' => max(30, (int) env('APPLICANT_VERIFIED_UNUSED_RETENTION_DAYS', 180)),
        'real_applicant_enabled' => (bool) env('APPLICANT_LIFECYCLE_CLEANUP_ENABLED', true),
        // Real applicant: akun login dipensiunkan saat seluruh periode selesai;
        // riwayat PPDB dan dokumen sekolah tetap mengikuti kebijakan arsip.
        'batch_size' => max(10, (int) env('APPLICANT_RETIREMENT_BATCH_SIZE', 100)),
        'daily_at' => env('APPLICANT_RETIREMENT_DAILY_AT', '03:30'),
    ],
];
