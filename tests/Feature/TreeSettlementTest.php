<?php

namespace Tests\Feature;

use App\Jobs\TreeSettlement;
use App\Tree;
use App\User;
use App\Wallet;
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
        $day,
        $gems
    )
    {
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

        $baseGemAmount = [];
        foreach (array_keys($gems) as $gem) {
            $baseGemAmount[$gem] = $user->wallets()->save(
                factory(Wallet::class)->make([
                    'gem' => $gem
                ])
            )->amount;
        }

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

        foreach ($gems as $gem => $expectedAmount) {
            $this->assertDatabaseHas(
                (new Wallet)->getTable(),
                [
                    'user_id' => $user->id,
                    'gem' => $gem,
                    'amount' => bcadd($baseGemAmount[$gem], $expectedAmount, 1),
                ]
            );
        }
    }

    public function dataProvider()
    {
        return [
            // original capacity, original progress, new capacity, new progress, Carbon test now
            [1, '0', 1, '14.3', 'monday', $this->gems('0', '0', '0', '0')],
            [1, '14.3', 1, '28.6', 'tuesday', $this->gems('0', '0', '0', '0')],
            [1, '28.6', 1, '42.9', 'wednesday', $this->gems('0', '0', '0', '0')],
            [1, '42.9', 1, '57.2', 'thursday', $this->gems('0', '0', '0', '0')],
            [1, '57.2', 1, '71.5', 'friday', $this->gems('0', '0', '0', '0')],
            [1, '71.5', 1, '85.8', 'saturday', $this->gems('0', '0', '0', '0')],
            [1, '85.8', 0, '0.0', 'sunday', $this->gems('17.5', '10.5', '3.5', '3.5')],

            [1, '99.0', 0, '0.0', 'saturday', $this->gems('17.5', '10.5', '3.5', '3.5')],
            [2, '99.0', 1, '13.3', 'saturday', $this->gems('17.5', '10.5', '3.5', '3.5')],

            [1, '750.0', 0, '0.0', 'saturday', $this->gems('17.5', '10.5', '3.5', '3.5')],
            [8, '750.0', 1, '64.3', 'saturday', $this->gems('122.5', '73.5', '24.5', '24.5')],
        ];
    }

    private function setTestNow($day)
    {
        return Carbon::setTestNow(
            Carbon::createFromTimestamp(strtotime($day))
        );
    }

    private function gems($qiCai, $duoXi, $duoFu, $duoCai)
    {
        return [
            Wallet::GEM_QI_CAI => $qiCai,
            Wallet::GEM_DUO_XI => $duoXi,
            Wallet::GEM_DUO_FU => $duoFu,
            Wallet::GEM_DUO_CAI => $duoCai,
        ];
    }
}
