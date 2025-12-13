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
        // Check pending payments
        // Every 1 minute in local, every 3 minutes in production
        $interval = app()->environment('local') ? 'everyMinute' : 'everyThreeMinutes';
        
        $schedule->command('payment:check')
            ->$interval()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\TelegramSetWebhook::class,
    ];
}
