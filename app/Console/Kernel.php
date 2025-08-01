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
        $schedule->command('reminders:send-followups')
        ->dailyAt('08:00')
        ->withoutOverlapping()
        ->runInBackground();

        // Use optimized version for better performance with large databases
        $schedule->command('deliveries:check-overdue-optimized --batch-size=200 --max-notifications=1000')
        ->dailyAt('09:00')
        ->withoutOverlapping()
        ->runInBackground()
        ->sendOutputTo(storage_path('logs/overdue-deliveries.log'))
        ->appendOutputTo(storage_path('logs/overdue-deliveries.log'));

        // Cleanup old notifications weekly to keep database lean
        $schedule->command('notifications:cleanup --days=30 --keep-unread=60')
        ->weeklyOn(1, '02:00') // Monday at 2 AM
        ->withoutOverlapping()
        ->runInBackground();
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
