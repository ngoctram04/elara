<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        /**
         * 🔥 DEV (TEST)
         */
        $schedule->command('inventory:destroy-expired')
            ->everyMinute()
            ->withoutOverlapping();

        /**
         * 🔥 PRODUCTION (NHỚ BẬT LẠI)
         */
        /*
        $schedule->command('inventory:destroy-expired')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer();
        */
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}