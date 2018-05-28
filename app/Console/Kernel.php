<?php

namespace App\Console;

use App\Jobs\DailySettlement;
use App\Jobs\SendTreeLowRemainReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (App::environment('production')) {
//            $schedule->job(new DailySettlement)->daily()->evenInMaintenanceMode();
//            $schedule->job(new SendTreeLowRemainReminders)->dailyAt('08:00')->evenInMaintenanceMode();
        } else {
//            $schedule->job(new DailySettlement)->everyTenMinutes()->evenInMaintenanceMode();
        }
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
