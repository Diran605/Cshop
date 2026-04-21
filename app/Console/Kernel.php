<?php

namespace App\Console;

use App\Console\Commands\SuggestExpiredItemsForClearance;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-suggest expired items for clearance every day at 6 AM
        $schedule->command(SuggestExpiredItemsForClearance::class)
            ->dailyAt('06:00')
            ->description('Auto-suggest expired/near-expiry items for clearance manager');

        // Alternatively, run every 2 hours during business hours:
        // $schedule->command(SuggestExpiredItemsForClearance::class)
        //     ->everyTwoHours()
        //     ->between('06:00', '18:00')
        //     ->description('Auto-suggest expired items hourly');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
