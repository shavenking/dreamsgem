<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\UserCreated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\UserUpdated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\DragonCreated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\DragonActivated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\TreeCreated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\TreeActivated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\TreeUpdated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\WalletUpdated' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\WalletRecalled' => [
            'App\Listeners\CreateOperationHistory',
        ],
        'App\Events\WalletTransferred' => [
            'App\Listeners\CreateOperationHistory',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
