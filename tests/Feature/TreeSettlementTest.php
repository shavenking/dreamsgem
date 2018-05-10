<?php

namespace Tests\Feature;

use App\Dragon;
use App\Jobs\TreeSettlement;
use App\SettlementHistory;
use App\Tree;
use App\TreeSettlementHistory;
use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class TreeSettlementTest extends TestCase
{
    use RefreshDatabase, OperationHistoryAssertTrait;

    public function testTreeSettlementNew()
    {
        $this->setTestNow('saturday');

        $user1Trees = $this->addTrees(
            $user1 = $this->createUser(),
            [
                'activated' => 4,
                'in_activated' => 1,
            ]
        );

        $user1Trees[0]->update(
            [
                'remain' => 10,
            ]
        );
        $user1Trees[1]->update(
            [
                'remain' => 40,
            ]
        );

        $user2Trees = $this->addTrees(
            $user2 = $this->createUser($user1),
            [
                'activated' => 1
            ]
        );

        $this->addTrees(
            $user3 = $this->createUser($user1, false),
            []
        );

        $user4 = $this->createUser($user2);
        $user5 = $this->createUser($user4);

        $user6Trees = $this->addTrees(
            $user6 = $this->createUser($user5),
            [
                'activated' => 1,
            ]
        );

        $user7 = $this->createUser($user6);
        $user8 = $this->createUser($user7);

        $user9Trees = $this->addTrees(
            $user9 = $this->createUser($user8),
            [
                'activated' => 1,
                'in_activated' => 1,
            ]
        );

        $user10 = $this->createUser($user9);

        $settlementHistory = SettlementHistory::create();
        foreach ([
                     $user10, $user9,
                     $user8, $user7,
                     $user6, $user5,
                     $user4, $user3,
                     $user2, $user1,
                 ] as $idx => $user) {
            (new TreeSettlement($user, $settlementHistory))->handle();
        }

        $user1TreesAssertions = [
            ['remain' => 0, 'capacity' => 90, 'progress' => '0', 'gain' => 10],
            ['remain' => 0, 'capacity' => 90, 'progress' => '0', 'gain' => 40],
            ['remain' => 80, 'capacity' => 90, 'progress' => '14.3', 'gain' => 10],
            ['remain' => 90, 'capacity' => 90, 'progress' => '0'],
            ['remain' => 90, 'capacity' => 90, 'progress' => '0'],
        ];

        $user2TreesAssertions = [
            ['remain' => 60, 'capacity' => 90, 'progress' => '0', 'gain' => 30],
        ];

        $user6TreesAssertions = [
            ['remain' => 60, 'capacity' => 90, 'progress' => '0', 'gain' => 30],
        ];

        $user9TreesAssertions = [
            ['remain' => 90, 'capacity' => 90, 'progress' => '14.3'],
            ['remain' => 90, 'capacity' => 90, 'progress' => '0'],
        ];

        foreach ([
                     [$user1Trees, $user1TreesAssertions],
                     [$user2Trees, $user2TreesAssertions],
                     [$user6Trees, $user6TreesAssertions],
                     [$user9Trees, $user9TreesAssertions],
                 ] as $parameters) {
            $this->assertTrees(...$parameters);
        }

        foreach ([
                     [$user1, $this->baseGemsCount($user1TreesAssertions)],
                     [$user2, $this->baseGemsCount($user2TreesAssertions)],
                     [$user6, $this->baseGemsCount($user6TreesAssertions)],
                     [$user9, $this->baseGemsCount($user9TreesAssertions)],
                 ] as $idx => $parameters) {
            $this->assertWallets(...$parameters);
        }
    }

    private function createUser(User $upline = null, $activated = true)
    {
        $user = factory(User::class)->create();

        if ($activated) {
            factory(Dragon::class)->create([
                'owner_id' => $user->id,
                'user_id' => $user->id,
            ]);
        }

        if ($upline) {
            $upline->appendNode($user);
        }

        return $user;
    }

    private function addTrees(User $user, $times)
    {
        $trees = collect();

        $trees = $trees->merge(
            factory(Tree::class)->times(data_get($times, 'activated', 0))->create([
                'owner_id' => $user->id,
                'user_id' => $user->id,
            ])
        );

        $trees = $trees->merge(
            factory(Tree::class)->times(data_get($times, 'in_activated', 0))->create([
                'owner_id' => $user->id,
                'user_id' => null,
            ])
        );

        return $trees;
    }

    private function setTestNow($day)
    {
        Carbon::setTestNow(
            $carbon = Carbon::createFromTimestamp(strtotime($day))
        );

        return $carbon;
    }

    private function gems($times)
    {
        return [
            Wallet::GEM_QI_CAI => bcmul('17.5', $times, 1),
            Wallet::GEM_DUO_XI => bcmul('10.5', $times, 1),
            Wallet::GEM_DUO_FU => bcmul('3.5', $times, 1),
            Wallet::GEM_DUO_CAI => bcmul('3.5', $times, 1),
        ];
    }

    private function assertTreeSettlementHistoryExists(User $user, Carbon $testNow, $attributes)
    {
        $settlementDailyKey = TreeSettlementHistory::KEY_SETTLEMENT_DAILY;
        $settlementDownlinesKey = TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES;

        $treeSettlementHistory = TreeSettlementHistory::whereUserId($user->id)->whereDate(
            'created_at', $testNow
        )->firstOrFail();

        $this->assertSame(
            [
                $settlementDailyKey => data_get($attributes, "progress_gained.$settlementDailyKey"),
                $settlementDownlinesKey => data_get($attributes, "progress_gained.$settlementDownlinesKey"),
            ],
            $treeSettlementHistory->progress_gained
        );
        $this->assertSame(
            [
                $settlementDownlinesKey => data_get($attributes, "maximum_progress_rule.$settlementDownlinesKey"),
            ],
            $treeSettlementHistory->maximum_progress_rule
        );
    }

    private function assertTrees($trees, $treesAssertions)
    {
        foreach ($trees as $idx => $tree) {
            $tree->refresh();
            $this->assertDatabaseHas(
                $tree->getTable(),
                array_merge(
                    ['id' => $tree->id],
                    array_only($treesAssertions[$idx], 'remain', 'capacity', 'progress')
                )
            );
        }
    }

    private function assertWallets($user, $times)
    {
        if (!$times) {
            return;
        }

        foreach ([
                     Wallet::GEM_QI_CAI => '17.5',
                     Wallet::GEM_DUO_XI => '10.5',
                     Wallet::GEM_DUO_FU => '3.5',
                     Wallet::GEM_DUO_CAI => '3.5',
                 ] as $gem => $base) {
            $this->assertDatabaseHas(
                (new Wallet)->getTable(),
                [
                    'user_id' => $user->id,
                    'gem' => $gem,
                    'amount' => bcmul($base, $times, 1),
                ]
            );
        }
    }

    private function baseGemsCount($userTreesAssertions)
    {
        return array_reduce($userTreesAssertions, function ($carry, $treesAssertion) {
            return $carry + data_get($treesAssertion, 'gain', 0);
        }, 0);
    }
}
