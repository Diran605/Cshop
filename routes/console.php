<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Clearance & Expiry Scheduling ───────────────────────────────
// Scan for products approaching expiry and auto-create clearance items
// Runs every 2 hours during business hours for timely detection
Schedule::command('clearance:scan-expiry')
    ->everyTwoHours()
    ->between('06:00', '22:00')
    ->description('Scan for expiring products and create clearance items');

// Auto-suggest expired/near-expiry items for clearance manager review
// Runs daily at 6 AM as a comprehensive sweep
Schedule::command('clearance:suggest-expired-items')
    ->dailyAt('06:00')
    ->description('Auto-suggest expired items for clearance manager');
