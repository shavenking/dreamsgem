<?php

namespace App\Policies;

use App\Tree;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TreePolicy
{
    use HandlesAuthorization;

    public function update(User $loggedInUser, Tree $tree)
    {
        return $loggedInUser->id === $tree->owner_id;
    }
}
