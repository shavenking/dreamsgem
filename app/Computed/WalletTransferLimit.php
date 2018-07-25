<?php

namespace App\Computed;

use App\User;
use App\Wallet;
use App\WalletTransferApplication;

class WalletTransferLimit
{
    public $hasLimit;

    public $max;

    public $used;

    public $remain;

    private $user;

    public function __construct(User $user, Wallet $wallet)
    {
        $this->user = $user;
        $this->hasLimit = true;
        $this->max = $this->remain = $this->used = '0';

        if ((int) $wallet->gem !== Wallet::GEM_C) {
            $this->hasLimit = false;
        }

        if (
            !$user->isAdmin()
            && !$user->is_child_account
            && $this->hasLimit
        ) {
            $this->compute();
        }
    }

    private function compute()
    {
        $this->max = collect([$this->user])
            ->merge($this->user->childAccounts)
            ->reduce(function ($carry, $user) {
                $quota = '0';

                if ($firstActivatedTree = $user->activatedTrees()->first()) {
                    $quota = $firstActivatedTree->quota();
                }

                return bcadd($carry, $quota, 1);
            }, '0');

        $gemCWallet = Wallet::where([
            ['gem', Wallet::GEM_C],
            ['user_id', $this->user->id],
        ])->firstOrFail();

        $this->used = WalletTransferApplication::where('to_wallet_id', $gemCWallet->id)->get()->reduce(function ($carry, $walletTransferApplication) {
            return bcadd(
                $carry,
                bcmul($walletTransferApplication->rate, $walletTransferApplication->amount, 1),
                1
            );
        }, '0');

        $this->remain = bcsub($this->max, $this->used, 1);

        if (bccomp($this->remain, '0', 1) < 0) {
            $this->remain = '0';
        }
    }
}