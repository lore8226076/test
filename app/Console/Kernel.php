<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:send-task-items')->dailyAt('23:59');
        $schedule->command('points:reset-all')->dailyAt('23:59');
        $schedule->command('mini-game:reset-rank')->dailyAt('23:59');
        $schedule->command('status:reset')->dailyAt('00:01');
        $schedule->command('users:delete-schedule')->dailyAt('00:02');
        $schedule->command('app:player-register-event')->dailyAt('00:05');
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
