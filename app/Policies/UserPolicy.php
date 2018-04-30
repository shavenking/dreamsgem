<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function createDragons(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }

    public function createTrees(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }

    public function createChildAccount(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }

    public function update(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }
}
