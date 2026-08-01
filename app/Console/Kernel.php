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
        $schedule->command('app:audit')->dailyAt('01:00');
        $schedule->command('app:check-bill-class')->everyTenMinutes();
        $schedule->command('db:sync-master')->dailyAt('01:00')->withoutOverlapping();
        // Cleanup SaldoHistory Kode Unik orphaned setiap hari pukul 02:00
        // Menggantikan lazy cleanup yang sebelumnya ada di GET request Dashboard (idempotency violation)
        $schedule->command('saldo:cleanup-orphaned-kode-unik')->dailyAt('02:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
