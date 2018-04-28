<?php

namespace App\Jobs;

use App\Tree;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TreeSettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Tree $tree */
        $tree = $this->user->trees()->where('capacity', '>', 0)->firstOrFail();

        $originalProgress = $tree->progress;
        $originalCapacity = $tree->capacity;

        $tree->progress = bcadd($tree->progress, [
            // sunday, monday, tuesday ... saturday
            '14.2', '14.3', '14.3', '14.3', '14.3', '14.3', '14.3',
        ][Carbon::now()->dayOfWeek], 1);

        if (bccomp($tree->progress, '100.0', 1) >= 0) {
            $tree->capacity -= 1;
            $tree->progress = $tree->capacity === 0 ? '0.0' : bcsub($tree->progress, '100.0', 1);
        }

        $affectedCount = Tree::whereId($tree->id)
            ->where('progress', $originalProgress)
            ->where('capacity', $originalCapacity)
            ->update([
                'capacity' => $tree->capacity,
                'progress' => $tree->progress
            ]);

        if ($affectedCount !== 1) {
            $this->fail('Data is changed during job.');
        }
    }
}
