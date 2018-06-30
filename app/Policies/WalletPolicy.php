<?php

namespace App\Policies;

use App\User;
use App\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    public function owns(User $user, Wallet $wallet)
    {
        return $this->userOwnsWallet($user, $wallet);
    }

    public function transfer(User $user, Wallet $wallet)
    {
        return $user->id === $wallet->user_id;
    }

    private function userOwnsWallet(User $user, Wallet $wallet)
    {
        return $user->id === $wallet->user_id;
    }
}
