<?php

namespace App\Jobs;

use App\SettlementHistory;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DailySettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (SettlementHistory::whereDate('created_at', Carbon::today('Asia/Taipei'))->exists()) {
            return;
        }

        $settlementHistory = SettlementHistory::create();
        foreach (User::all() as $user) {
            dispatch(new TreeSettlement($user, $settlementHistory));
        }
    }
}
