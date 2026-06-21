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
        /*
        |--------------------------------------------------------------------------
        | MIKHMON FLOW (Voucher Sales)
        |--------------------------------------------------------------------------
        */
        $schedule->command('mikhmon:import')->dailyAt('11:08');
        $schedule->command('mikhmon:transform')->dailyAt('11:10');
        $schedule->command('mikhmon:aggregate-daily')->dailyAt('11:12');
        $schedule->command('mikhmon:journalize {date?}')->dailyAt('11:14');

        /*
        |--------------------------------------------------------------------------
        | BEAT FLOW (ISP Subscriptions)
        |--------------------------------------------------------------------------
        */
        $schedule->command('beat:import-raw')->dailyAt('11:16');
        $schedule->command('beat:transform-staging')->dailyAt('11:18');
        $schedule->command('beat:generate-invoices')->dailyAt('11:20');
        $schedule->command('beat:record-payment')->dailyAt('11:22');
        $schedule->command('journal:beat-invoice')->dailyAt('11:24');
        $schedule->command('journal:beat-payment')->dailyAt('11:26');
        $schedule->command('beat:post-journals')->dailyAt('11:28');

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS TRANSAKSI
        |--------------------------------------------------------------------------
        */
        $schedule->command('transaksi:update-status')->dailyAt('00:01');
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
