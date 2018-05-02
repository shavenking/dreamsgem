<?php

namespace App\Jobs;

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
use Illuminate\Support\Facades\DB;

class TreeSettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    private $updatedTrees;

    private $updatedWallets;

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
        // if children of user not settled, release with delay
        foreach ($this->user->children as $child) {
            /** @var TreeSettlementHistory $treeSettlementHistoryToday */
            $treeSettlementHistoryToday = $child->treeSettlementHistories()
                ->whereDate('created_at', Carbon::now())
                ->first();

            if (!$treeSettlementHistoryToday) {
                $this->release(60);

                return;
            }
        }

        DB::beginTransaction();

        /** @var Tree $tree */
        $trees = $this->user->trees()->where('remain', '>', 0)->get();

        $progressGained = $this->progressGained();
        $maximumProgressRule = [
            TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => $this->maximumProgressRule($this->user->children->count()),
        ];

        $this->user->treeSettlementHistories()->create([
            'progress_gained' => $progressGained,
            'maximum_progress_rule' => $maximumProgressRule,
        ]);

        $remainProgress = bcadd(
            $progressGained[TreeSettlementHistory::KEY_SETTLEMENT_DAILY],
            $progressGained[TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES],
            1
        );

        try {
            foreach ($trees as $tree) {
                $remainProgress = $this->settleTree($tree, $remainProgress);
            }

            foreach ($this->updatedTrees as $tree) {
                event(new TreeUpdated($tree));
            }

            foreach ($this->updatedWallets as $wallet) {
                event(new WalletUpdated($wallet));
            }
        } catch (\Throwable $e) {
            $this->release();

            return;
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
        if (bccomp($remainProgress, '0', 1) <= 0) {
            return '0';
        }

        $originalProgress = $tree->progress;
        $remain = $tree->remain;

        $totalProgress = bcadd($originalProgress, $remainProgress, 1);
        $award = min(bcdiv($totalProgress, '100.0', 0), $remain);
        $remainProgress = bcsub($totalProgress, bcmul($award, '100.0', 1), 1);

        if ($award) {
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

        $tree->remain -= $award;
        $tree->progress = $tree->remain === 0 ? '0' : $remainProgress;

        $affectedCount = Tree::whereId($tree->id)
            ->where('progress', $originalProgress)
            ->where('remain', $remain)
            ->update(
                [
                    'remain' => $tree->remain,
                    'progress' => $tree->progress,
                ]
            );

        throw_if(
            $affectedCount !== 1,
            new \RuntimeException('Tree data has been changed')
        );

        $this->updatedTrees[$tree->id] = $tree->refresh();

        if ($tree->remain !== 0) {
            return '0';
        }

        return $remainProgress;
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

    private function progressGained()
    {
        $dailyProgress = [
            // sunday, monday, tuesday ... saturday
            '14.2',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
            '14.3',
        ][Carbon::now()->dayOfWeek];

        $nLevel = [
                0 => 0,
                1 => 5,
                2 => 8,
                3 => 10,
                4 => 10,
                5 => 10,
                6 => 10,
                7 => 10,
            ][$this->user->children->count()] - 1;

        $children = collect();
        $candidateChildren = collect($this->user->children);

        while ($nLevel-- > 0) {
            while ($child = $candidateChildren->pop()) {
                $candidateChildren->merge($child->children);
                $children->push($child);
            }
        }

        $downlinesProgress = $children->reduce(
            function ($carry, User $child) {
                /** @var TreeSettlementHistory $treeSettlementHistoryToday */
                $treeSettlementHistoryToday = $child->treeSettlementHistories()
                    ->whereDate('created_at', Carbon::now())
                    ->firstOrFail();
                $settlementDailyKey = TreeSettlementHistory::KEY_SETTLEMENT_DAILY;
                $settlementDownlinesKey = TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES;
                $childTotalProgressGained = bcadd(
                    data_get($treeSettlementHistoryToday, "progress_gained.$settlementDailyKey"),
                    data_get($treeSettlementHistoryToday, "progress_gained.$settlementDownlinesKey"),
                    1
                );

                return bcadd($carry, $childTotalProgressGained, 1);
            }, '0'
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
        ][$this->user->children->count()];

        return [
            TreeSettlementHistory::KEY_SETTLEMENT_DAILY => $dailyProgress,
            TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => min(
                bcmul($downlinesProgress, $multiplier, 1), $this->maximumProgressRule($this->user->children->count())
            ),
        ];
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
            ], $childrenCount, '3000.0'
        );
    }
}
