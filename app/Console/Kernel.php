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
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->runDailySettlement($schedule);
        $this->sendTreeLowRemainReminders($schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    private function runDailySettlement(Schedule $schedule)
    {
        if (!$this->canRunDailySettlement()) {
            return;
        }

        // 24:05 Asia/Taipei
        $schedule
            ->job(new DailySettlement)
            ->dailyAt('16:05')
            ->evenInMaintenanceMode();
    }

    private function sendTreeLowRemainReminders(Schedule $schedule)
    {
        if (!$this->canRunSendTreeLowRemainReminders()) {
            return;
        }

        // 08:00 Asia/Taipei
        $schedule
            ->job(new SendTreeLowRemainReminders)
            ->dailyAt('00:00')
            ->evenInMaintenanceMode();
    }

    private function canRunDailySettlement()
    {
        return App::environment(['production', 'staging']);
    }

    private function canRunSendTreeLowRemainReminders()
    {
        return App::environment('production');
    }
}
