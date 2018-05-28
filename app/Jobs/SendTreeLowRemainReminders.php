<?php

namespace App\Jobs;

use App\Mail\TreeLowRemainReminder;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTreeLowRemainReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereHas('activatedDragon')->whereNull('user_id')->get();

        foreach ($users as $user) {
            if (($remain = $user->activatedTrees()->sum('remain')) < User::TREE_LOW_REMAIN) {
                Mail::to($user)->send(new TreeLowRemainReminder($remain));
            }
        }
    }
}
