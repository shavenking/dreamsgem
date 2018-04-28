<?php

namespace App\Jobs;

use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();

        /** @var Tree $tree */
        $tree = $this->user->trees()->where('capacity', '>', 0)->firstOrFail();

        $originalProgress = $tree->progress;
        $originalCapacity = $tree->capacity;

        $tree->progress = bcadd($tree->progress, [
            // sunday, monday, tuesday ... saturday
            '14.2', '14.3', '14.3', '14.3', '14.3', '14.3', '14.3',
        ][Carbon::now()->dayOfWeek], 1);

        if (bccomp($tree->progress, '100.0', 1) >= 0) {
            $award = min(bcdiv($tree->progress, '100.0', 0), $tree->capacity);

            $tree->capacity -= $award;
            $tree->progress = $tree->capacity === 0 ? '0.0' : bcmod($tree->progress, '100.0', 1);

            foreach ([
                Wallet::GEM_QI_CAI => bcmul('17.5', $award, 1),
                Wallet::GEM_DUO_XI => bcmul('10.5', $award, 1),
                Wallet::GEM_DUO_FU => bcmul('3.5', $award, 1),
                Wallet::GEM_DUO_CAI => bcmul('3.5', $award, 1),
            ] as $gem => $increment) {
                if ($this->createOrIncrementWallet($gem, $increment) !== 1) {
                    $this->fail('Wallet data is changed during job.');
                    return;
                }
            }
        }

        $affectedCount = Tree::whereId($tree->id)
            ->where('progress', $originalProgress)
            ->where('capacity', $originalCapacity)
            ->update([
                'capacity' => $tree->capacity,
                'progress' => $tree->progress
            ]);

        if ($affectedCount !== 1) {
            $this->fail('Tree data is changed during job.');
            return;
        }

        DB::commit();
    }

    private function createOrIncrementWallet($gem, $increment)
    {
        $wallet = $this->user->wallets()->firstOrCreate([
            'gem' => $gem
        ], [
            'amount' => '0'
        ]);

        $affectedCount = Wallet::whereId($wallet->id)
            ->where('gem', $wallet->gem)
            ->where('amount', $wallet->amount)
            ->update([
                'amount' => bcadd($wallet->amount, $increment, 1),
            ]);

        return $affectedCount;
    }
}
