<?php

namespace Tests\Feature;

use App\Jobs\DailySettlement;
use App\Jobs\TreeSettlement;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DailySettlementTest extends TestCase
{
    use RefreshDatabase;

    public function testDailySettlement()
    {
        $users = factory(User::class)->times(3)->create();

        Queue::fake();

        /** @var DailySettlement $job */
        $job = app(DailySettlement::class);

        $job->handle();

        Queue::assertPushed(TreeSettlement::class, 3);
        Queue::assertPushed(TreeSettlement::class, function (TreeSettlement $job) use ($users) {
            return $users->first(function ($user) use ($job) {
                return $user->id === $job->user->id;
            }, false);
        });
    }
}
