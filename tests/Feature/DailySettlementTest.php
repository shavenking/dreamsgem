<?php

namespace Tests\Feature;

use App\Jobs\DailySettlement;
use App\Jobs\TreeSettlement;
use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DailySettlementTest extends TestCase
{
    use DatabaseTransactions;

    public function testDailySettlement()
    {
        /** @var Collection $users */
        $users = factory(User::class)->times(2)->create()->each(function (User $user) {
            $user->trees()->save(
                factory(Tree::class)->states('capacity_available')->make()
            );
        });

        factory(User::class)->create()->trees()->save(
            factory(Tree::class)->make()
        );

        Queue::fake();

        /** @var DailySettlement $job */
        $job = app(DailySettlement::class);

        $job->handle();

        Queue::assertPushed(TreeSettlement::class, $users->count());
        Queue::assertPushed(TreeSettlement::class, function (TreeSettlement $job) use ($users) {
            return $users->first(function ($user) use ($job) {
                return $user->id === $job->user->id;
            }, false);
        });
    }
}
