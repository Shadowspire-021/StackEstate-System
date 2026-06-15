<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\BackupJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily database backup at 2:00 AM
        $schedule->job(new BackupJob)->dailyAt('02:00')->withoutOverlapping();

        // Check for overdue installments daily at 8:00 AM
        $schedule->command('installments:check-overdue')->dailyAt('08:00');

        // Apply late fees to overdue installments daily at 8:30 AM
        $schedule->command('installments:apply-late-fees')->dailyAt('08:30');

        // Check for upcoming due installments and send reminders daily at 9:00 AM
        $schedule->command('installments:check-upcoming-due')->dailyAt('09:00');
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
