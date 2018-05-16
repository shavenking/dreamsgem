<?php

namespace App\Policies;

use App\User;
use App\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    public function transfer(User $user, Wallet $wallet)
    {
        return $user->id === $wallet->user_id;
    }
}
