<?php

namespace App\Jobs;

use App\SettlementHistory;
use App\Events\TreeUpdated;
use App\Events\WalletUpdated;
use App\Tree;
use App\TreeSettlementHistory;
use App\User;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreeSettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    private $updatedTrees;

    private $updatedWallets;

    private $settlementHistory;

    private $award;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param SettlementHistory $settlementHistory
     */
    public function __construct(User $user, SettlementHistory $settlementHistory)
    {
        $this->user = $user;
        $this->settlementHistory = $settlementHistory;
        $this->updatedTrees = [];
        $this->updatedWallets = [];
        $this->award = 0;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // if children of user not settled, release with delay
        foreach ($this->user->children as $child) {
            /** @var TreeSettlementHistory $treeSettlementHistory */
            $treeSettlementHistory = $this->settlementHistory->treeSettlementHistories()->where([
                'user_id' => $child->id,
            ])->first();

            if (!$treeSettlementHistory) {
                $this->release(1);

                return;
            }
        }

        DB::beginTransaction();

        try {
            /** @var Tree $tree */
            $trees = $this->user->activatedTrees()->where('remain', '>', 0)->orderBy('id')->get();

            $totalDailyProgressGained = $this->settleDailyTreeProgress($trees);
            $totalDownlinesProgressGained = $this->downlinesProgress();
            $remainProgress = $totalDownlinesProgressGained;
            foreach ($trees as $tree) {
                $remainProgress = $this->settleTree($tree, $remainProgress);
            }

            $activatedChildrenCount = $this->user->children->filter(function (User $user) {
                return $user->activated;
            })->count();
            $this->user->treeSettlementHistories()->create([
                'settlement_history_id' => $this->settlementHistory->id,
                'progress_gained' => [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => $totalDailyProgressGained,
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => $totalDownlinesProgressGained,
                    TreeSettlementHistory::KEY_SETTLEMENT_AWARD => $this->award,
                ],
                'maximum_progress_rule' => [
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => $this->maximumProgressRule($activatedChildrenCount),
                ],
            ]);

            foreach ($this->updatedTrees as $tree) {
                event(new TreeUpdated($tree));
            }

            foreach ($this->updatedWallets as $wallet) {
                event(new WalletUpdated($wallet));
            }
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            $this->release(1);
        }

        DB::commit();
    }

    /**
     * @param Tree $tree
     * @param $remainProgress
     *
     * @return string
     * @throws \Throwable
     */
    private function settleTree(Tree $tree, $remainProgress)
    {
        if (bccomp($remainProgress, '0', 1) <= 0 && bccomp($tree->progress, '100.0', 1) < 0) {
            return '0';
        }

        $totalTreeProgress = bcadd($tree->progress, $remainProgress, 1);
        $award = (int) min(
            $tree->remain,
            bcdiv($totalTreeProgress, '100', 0)
        );
        $remainProgress = bcsub($totalTreeProgress, bcmul($award, '100.0', 1), 1);

        $this->award += $award;

        if (bccomp($award, '0.0', 1) > 0) {
            foreach ([
                         Wallet::GEM_QI_CAI => bcmul('17.5', $award, 1),
                         Wallet::GEM_DUO_XI => bcmul('10.5', $award, 1),
                         Wallet::GEM_DUO_FU => bcmul('3.5', $award, 1),
                         Wallet::GEM_DUO_CAI => bcmul('3.5', $award, 1),
                     ] as $gem => $increment) {
                throw_if(
                    $this->createOrIncrementWallet($gem, $increment) !== 1,
                    new \RuntimeException('Wallet data has been changed')
                );
            }
        }

        $this->updateTree($tree, [
            'remain' => $remain = $tree->remain - $award,
            'progress' => $remain === 0 ? '0' : $remainProgress,
        ]);

        return $remain !== 0 ? '0' : $remainProgress;
    }

    private function createOrIncrementWallet($gem, $increment)
    {
        $wallet = $this->user->wallets()->firstOrCreate(
            [
                'gem' => $gem,
            ], [
                'amount' => '0',
            ]
        );

        $affectedCount = Wallet::whereId($wallet->id)
            ->where('gem', $wallet->gem)
            ->where('amount', $wallet->amount)
            ->update(
                [
                    'amount' => bcadd($wallet->amount, $increment, 1),
                ]
            );

        $this->updatedWallets[$wallet->id] = $wallet->refresh();

        return $affectedCount;
    }

    /**
     * @return array
     */
    private function downlinesProgress()
    {
        $activatedChildrenCount = $this->user->children->filter(function (User $child) {
            return $child->activated;
        })->count();

        $nLevel = [
                0 => 0,
                1 => 5,
                2 => 8,
                3 => 10,
                4 => 10,
                5 => 10,
                6 => 10,
                7 => 10,
            ][$activatedChildrenCount];

        $children = collect();
        $candidateChildren = collect($this->user->children);

        while ($nLevel-- > 0) {
            $oneLevelDownChildren = collect();

            while ($child = $candidateChildren->shift()) {
                $oneLevelDownChildren = $oneLevelDownChildren->concat($child->children);
                $children->push($child);
            }

            $candidateChildren = collect($oneLevelDownChildren);
        }

        $downlinesAward = $children->reduce(
            function ($carry, User $child) {
                if (!$child->activated) {
                    return $carry;
                }

                /** @var TreeSettlementHistory $treeSettlementHistoryToday */
                $treeSettlementHistory = $this->settlementHistory->treeSettlementHistories()->where([
                    'user_id' => $child->id,
                ])->first();
                $settlementAwardKey = TreeSettlementHistory::KEY_SETTLEMENT_AWARD;

                return $carry + data_get($treeSettlementHistory, "progress_gained.$settlementAwardKey", 0);
            }, 0
        );

        $multiplier = [
            0 => '0',
            1 => '10',
            2 => '10',
            3 => '11',
            4 => '12',
            5 => '13',
            6 => '14',
            7 => '15',
        ][$activatedChildrenCount];

        return min(
            bcmul($downlinesAward, $multiplier, 1), $this->maximumProgressRule($activatedChildrenCount)
        );
    }

    public function maximumProgressRule($childrenCount)
    {
        return data_get(
            [
                1 => '3000.0',
                2 => '6000.0',
                3 => '9000.0',
                4 => '12000.0',
                5 => '15000.0',
                6 => '18000.0',
                7 => '21000.0',
            ], $childrenCount, '0.0'
        );
    }

    private function settleDailyTreeProgress(Collection $trees)
    {
        $totalProgressGained = '0';

        foreach ($trees->take(3) as $tree) {
            $totalProgressGained = bcadd($totalProgressGained, $this->dailyProgress(), 1);

            $treeProgress = bcadd($tree->progress, $this->dailyProgress(), 1);
            $award = bccomp($treeProgress, '100.0', 1) > 0 ? min(bcdiv($treeProgress, '100.0', 0), $tree->remain) : 0;
            $this->award += $award;

            if (bccomp($award, '0.0', 1) > 0) {
                foreach ([
                             Wallet::GEM_QI_CAI => bcmul('17.5', $award, 1),
                             Wallet::GEM_DUO_XI => bcmul('10.5', $award, 1),
                             Wallet::GEM_DUO_FU => bcmul('3.5', $award, 1),
                             Wallet::GEM_DUO_CAI => bcmul('3.5', $award, 1),
                         ] as $gem => $increment) {
                    throw_if(
                        $this->createOrIncrementWallet($gem, $increment) !== 1,
                        new \RuntimeException('Wallet data has been changed')
                    );
                }
            }

            $this->updateTree($tree, [
                'remain' => $remain = $tree->remain - $award,
                'progress' => $remain === 0 ? '0' : bcsub($treeProgress, bcmul($award, '100.0', 1), 1),
            ]);
        }

        return $totalProgressGained;
    }

    private function updateTree(Tree $tree, $attributes)
    {
        Tree::where(['id' => $tree->id])->lockForUpdate();
        $tree->update($attributes);

        $this->updatedTrees[$tree->id] = $tree;

        return $tree;
    }

    private function dailyProgress()
    {
        return [
            // sunday, monday, tuesday ... saturday
            '14.2',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
        ][Carbon::now()->dayOfWeek];
    }
}
