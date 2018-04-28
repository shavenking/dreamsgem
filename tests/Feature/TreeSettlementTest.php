<?php

namespace Tests\Feature;

use App\Jobs\TreeSettlement;
use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TreeSettlementTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider dataProvider
     * @param $originalCapacity
     * @param $originalProgress
     * @param $newCapacity
     * @param $newProgress
     * @param $day
     */
    public function testTreeSettlement(
        $originalCapacity,
        $originalProgress,
        $newCapacity,
        $newProgress,
        $day
    ) {
        $this->setTestNow($day);

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->trees()->save(
            /** @var Tree $tree */
            $tree = factory(Tree::class)->states('capacity_available')->make([
                'capacity' => $originalCapacity,
                'progress' => $originalProgress,
            ])
        );

        /** @var TreeSettlement $job */
        $job = app(TreeSettlement::class, compact('user'));

        $job->handle();

        $this->assertDatabaseHas(
            $tree->getTable(),
            [
                'id' => $tree->id,
                'user_id' => $tree->user_id,
                'capacity' => $newCapacity,
                'progress' => $newProgress,
            ]
        );
    }

    public function dataProvider()
    {
        return [
            // original capacity, original progress, new capacity, new progress, Carbon test now
            [1, '0', 1, '14.3', 'monday'],
            [1, '14.3', 1, '28.6', 'tuesday'],
            [1, '28.6', 1, '42.9', 'wednesday'],
            [1, '42.9', 1, '57.2', 'thursday'],
            [1, '57.2', 1, '71.5', 'friday'],
            [1, '71.5', 1, '85.8', 'saturday'],
            [1, '85.8', 0, '0.0', 'sunday'],

            [1, '99.0', 0, '0.0', 'saturday'],
            [2, '99.0', 1, '13.3', 'saturday'],
        ];
    }

    private function setTestNow($day)
    {
        return Carbon::setTestNow(
            Carbon::createFromTimestamp(strtotime($day))
        );
    }
}
