<?php

namespace App\Providers;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;

class EloquentWithAdminScopeUserProvider extends EloquentUserProvider
{
    public function createModel()
    {
        $model = parent::createModel();

//        User::addAdminGlobalScope();

        return $model;
    }
}
