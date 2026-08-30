<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule hourly purge of unconfirmed orphan media uploads (CDC §4 / BE-21)
Schedule::command('media:purge-orphans')->hourly();

// Schedule nightly aggregation of car events and 12-month purge (CDC §4 / BE-38)
Schedule::command('cars:aggregate-events')->daily();
