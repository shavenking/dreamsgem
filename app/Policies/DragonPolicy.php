<?php

namespace App\Policies;

use App\Dragon;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DragonPolicy
{
    use HandlesAuthorization;

    public function update(User $loggedInUser, Dragon $dragon)
    {
        return $loggedInUser->id === $dragon->owner_id;
    }
}
