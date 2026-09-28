<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('backup.enabled')) {
    Schedule::command('app:backup')->dailyAt((string) config('backup.daily_at', '02:00'))->withoutOverlapping()->onOneServer();
}

if (config('retention.logs.enabled')) {
    Schedule::command('app:retention:logs')->dailyAt((string) config('retention.logs.daily_at', '03:00'))->withoutOverlapping()->onOneServer();
}

if (config('retention.trash.enabled')) {
    Schedule::command('app:trash:purge')->dailyAt((string) config('retention.trash.daily_at', '03:15'))->withoutOverlapping()->onOneServer();
}

if (config('retention.applicants.enabled')) {
    Schedule::command('app:applicants:retire')->dailyAt((string) config('retention.applicants.daily_at', '03:30'))->withoutOverlapping()->onOneServer();
}
