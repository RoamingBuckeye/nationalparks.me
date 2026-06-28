<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Monthly full mirror of NPS park + POI data. Picks up new parks, POIs,
// activities, operating hours, fees, etc.
Schedule::command('nps:sync')
    ->monthlyOn(1, '04:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();

// Daily alert refresh — alerts change often (closures, advisories) and
// belong on a much tighter cadence than the rest of the mirror.
Schedule::command('nps:sync alerts')
    ->dailyAt('05:00')
    ->onOneServer()
    ->withoutOverlapping();
