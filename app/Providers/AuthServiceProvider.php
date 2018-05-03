<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\User' => 'App\Policies\UserPolicy',
        'App\Dragon' => 'App\Policies\DragonPolicy',
        'App\Tree' => 'App\Policies\TreePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensCan([
            'create-dragons' => 'Create dragons',
            'create-trees' => 'Create trees',
            'create-child-accounts' => 'Create child accounts',
            'update-child-accounts' => 'Update child accounts',
            'update-profile' => 'Update profile',
        ]);
    }
}
