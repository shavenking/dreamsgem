<?php

namespace App\SettleUtils;

use App\Tree;
use App\User;
use App\Wallet;

class TreeSettle
{
    public $user;
    public $treeSettleResult;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->treeSettleResult = new TreeSettleResult;
    }

    public function with($progress)
    {
        $this->treeSettleResult = new TreeSettleResult;

        $trees = $this->user->activatedTrees()->treeSettleCandidates()->get();

        foreach ($trees as $tree) {
            $progress = $this->settleTree($tree, $progress);
        }

        return $this;
    }

    private function settleTree(Tree $tree, $remainProgress)
    {
        if (bccomp($remainProgress, '0', 1) <= 0 && bccomp($tree->progress, '100.0', 1) < 0) {
            return '0';
        }

        $totalTreeProgress = bcadd($tree->progress, $remainProgress, 1);
        $award = (int)min(
            $tree->remain,
            bcdiv($totalTreeProgress, '100', 0)
        );
        $award = $tree->multiplyAward($award);
        $remainProgress = bcsub($totalTreeProgress, bcmul($award, '100.0', 1), 1);

        $this->treeSettleResult->award += $award;

        if (bccomp($award, '0.0', 1) > 0) {
            foreach ([
                         Wallet::GEM_QI_CAI => bcmul('17.5', $award, 1),
                         Wallet::GEM_DUO_XI => bcmul('3.5', $award, 1),
                         Wallet::GEM_DUO_FU => bcmul('10.5', $award, 1),
                         Wallet::GEM_DUO_CAI => bcmul('3.5', $award, 1),
                     ] as $gem => $increment) {
                throw_if(
                    $this->createOrIncrementWallet($gem, $increment) !== 1,
                    new \RuntimeException('Wallet data has been changed')
                );
            }
        }

        $this->treeSettleResult->updatedTrees[$tree->id] = $this->updateTree($tree, [
            'remain' => $remain = $tree->remain - $award,
            'progress' => $remain === 0 ? '0' : (bccomp($remainProgress, '0.0', 1) < 0 ? '0' : $remainProgress),
        ]);

        return $remain !== 0 ? '0' : $remainProgress;
    }

    private function updateTree(Tree $tree, $attributes)
    {
        Tree::where(['id' => $tree->id])->lockForUpdate();

        $tree->update($attributes);

        return $tree;
    }

    private function createOrIncrementWallet($gem, $increment)
    {
        $wallet = $this->user->wallets()->whereGem($gem)->firstOrFail();

        $affectedCount = Wallet::whereId($wallet->id)
            ->where('gem', $wallet->gem)
            ->where('amount', $wallet->amount)
            ->update(
                [
                    'amount' => bcadd($wallet->amount, $increment, 1),
                ]
            );

        $this->treeSettleResult->updatedWallets[$wallet->id] = $wallet->refresh();

        return $affectedCount;
    }
}