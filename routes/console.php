<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Daily automated SEO audit (report viewable at /admin/seo)
Schedule::command('seo:audit')->dailyAt('03:00')->withoutOverlapping();

// Apply safe auto-fixes just before the daily audit
Schedule::command('seo:fix')->dailyAt('03:30')->withoutOverlapping();
