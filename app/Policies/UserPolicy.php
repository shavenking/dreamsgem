<?php

namespace App\Policies;

use App\Dragon;
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

    public function updateChildAccounts(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->user_id;
    }

    public function recall(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id;
    }

    public function listOperationHistories(User $loggedInUser, User $targetUser)
    {
        return $loggedInUser->id === $targetUser->id || $loggedInUser->id === $targetUser->user_id;
    }

    public function getTreeStats(User $loggedInUser, User $targetUser)
    {
        return (
            $loggedInUser->id === $targetUser->id
            || $loggedInUser->id === $targetUser->user_id // is my child account
            || $targetUser->isChildOf($loggedInUser) // downlines
        );
    }
}
