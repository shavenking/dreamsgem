<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function createChildAccount(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }
}
