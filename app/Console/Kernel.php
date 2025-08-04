<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\AutoCheckout::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('attendance:auto-checkout')
            ->dailyAt('00:00')
            ->timezone('Asia/Kolkata');

        // Schedule the daily attendance report to run at 00:30 IST, except on Mondays
        $schedule->command('attendance:send-daily-report pdf')
                 ->dailyAt('00:30')
                 ->timezone('Asia/Kolkata')
                 ->when(function () {
                     // Skip if today is Monday (previous day is Sunday, an off day)
                     return Carbon::now('Asia/Kolkata')->dayOfWeek !== Carbon::MONDAY;
                 })
                 ->appendOutputTo(storage_path('logs/cron.log'));
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
