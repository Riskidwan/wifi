<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
            // Jalankan setiap jam
    $schedule->command('billing:disable-overdue')->hourly();
          $schedule->command('invoices:check-overdue')->hourly();
        // $schedule->command('inspire')->hourly();
        // Kirim reminder WA setiap pagi jam 9
    $schedule->command('invoices:send-unpaid')->dailyAt('09:00');
}
    

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    }
