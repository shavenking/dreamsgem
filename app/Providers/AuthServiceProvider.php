<?php

namespace App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;

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
        'App\Wallet' => 'App\Policies\WalletPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $grant = new ExtensionGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        app(AuthorizationServer::class)->enableGrantType(
            $grant, Passport::tokensExpireIn()
        );

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
