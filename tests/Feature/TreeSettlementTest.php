<?php

namespace Tests\Feature;

use App\Jobs\TreeSettlement;
use App\OperationHistory;
use App\Tree;
use App\TreeSettlementHistory;
use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class TreeSettlementTest extends TestCase
{
    use DatabaseTransactions, OperationHistoryAssertTrait;

    /**
     * @dataProvider dataProvider
     *
     * @param $originalCapacity
     * @param $originalProgress
     * @param $remain
     * @param $newProgress
     * @param $day
     * @param $gems
     * @param $progressGained
     * @param $maximumProgressRule
     */
    public function testTreeSettlement(
        $originalCapacity,
        $originalProgress,
        $remain,
        $newProgress,
        $day,
        $gems,
        $progressGained,
        $maximumProgressRule
    ) {
        $testNow = $this->setTestNow($day);

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->trees()->save(
        /** @var Tree $tree */
            $tree = factory(Tree::class)->states('capacity_available')->make(
                [
                    'remain' => $originalCapacity,
                    'capacity' => $originalCapacity,
                    'progress' => $originalProgress,
                ]
            )
        );

        $baseGemAmount = [];
        foreach (array_keys($gems) as $gem) {
            $baseGemAmount[$gem] = $user->wallets()->save(
                factory(Wallet::class)->make(
                    [
                        'gem' => $gem,
                    ]
                )
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
                'remain' => $remain,
                'capacity' => $originalCapacity,
                'progress' => $newProgress,
            ]
        );
        $this->assertTreeSettlementHistoryExists(
            $user,
            $testNow,
            [
                'progress_gained' => $progressGained,
                'maximum_progress_rule' => $maximumProgressRule,
            ]
        );
        $this->assertOperationHistoryExists(
            $tree->refresh(),
            OperationHistory::TYPE_UPDATE
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

            if (bccomp($expectedAmount, '0', 1) !== 0) {
                $this->assertOperationHistoryExists(
                    Wallet::where(['user_id' => $user->id, 'gem' => $gem])->firstOrFail(),
                    OperationHistory::TYPE_UPDATE
                );
            }
        }
    }

    /**
     * @dataProvider settleNextDataProvider
     *
     * @param $day
     * @param $gems
     * @param $originalDataSet
     * @param $resultDataSet
     * @param $progressGained
     * @param $maximumProgressRule
     */
    public function testItWillSettleNextTreeIfPossible(
        $day,
        $gems,
        $originalDataSet,
        $resultDataSet,
        $progressGained,
        $maximumProgressRule
    ) {
        $testNow = $this->setTestNow($day);

        /** @var User $user */
        $user = factory(User::class)->create();

        $originalTrees = [];
        foreach ($originalDataSet as $treeData) {
            $tree = factory(Tree::class)->make(
                [
                    'remain' => $treeData['capacity'],
                    'capacity' => $treeData['capacity'],
                    'progress' => $treeData['progress'],
                ]
            );

            $originalTrees[] = $tree;

            $user->trees()->save($tree);
        }

        $baseGemAmount = [];
        foreach (array_keys($gems) as $gem) {
            $baseGemAmount[$gem] = $user->wallets()->save(
                factory(Wallet::class)->make(
                    [
                        'gem' => $gem,
                    ]
                )
            )->amount;
        }

        /** @var TreeSettlement $job */
        $job = app(TreeSettlement::class, compact('user'));

        $job->handle();

        $this->assertTreeSettlementHistoryExists(
            $user,
            $testNow,
            [
                'progress_gained' => $progressGained,
                'maximum_progress_rule' => $maximumProgressRule,
            ]
        );

        foreach ($originalTrees as $idx => $tree) {
            $this->assertDatabaseHas(
                $tree->getTable(),
                [
                    'id' => $tree->id,
                    'user_id' => $tree->user_id,
                    'remain' => $resultDataSet[$idx]['remain'],
                    'capacity' => $originalDataSet[$idx]['capacity'],
                    'progress' => $resultDataSet[$idx]['progress'],
                ]
            );

            if ($originalDataSet[$idx]['capacity'] > $resultDataSet[$idx]['remain']) {
                $this->assertOperationHistoryExists(
                    $tree->refresh(),
                    OperationHistory::TYPE_UPDATE
                );
            }
        }

        foreach ($gems as $gem => $expectedAmount) {
            $this->assertDatabaseHas(
                (new Wallet)->getTable(),
                [
                    'user_id' => $user->id,
                    'gem' => $gem,
                    'amount' => bcadd($baseGemAmount[$gem], $expectedAmount, 1),
                ]
            );

            if (bccomp($expectedAmount, '0', 1) !== 0) {
                $this->assertOperationHistoryExists(
                    Wallet::where(['user_id' => $user->id, 'gem' => $gem])->firstOrFail(),
                    OperationHistory::TYPE_UPDATE
                );
            }
        }
    }

    public function testDownlinesBonus()
    {
        $testNow = $this->setTestNow('sunday');

        $rootUser = factory(User::class)->create();

        $trees = [];
        foreach ([
            ['remain' => 2, 'capacity' => 2, 'progress' => '0'],
            ['remain' => 1, 'capacity' => 1, 'progress' => '0'],
            ['remain' => 1, 'capacity' => 1, 'progress' => '0'],
        ] as $tree) {
            $trees[] = $rootUser->trees()->create($tree);
        }

        $firstLevelLeftUser = factory(User::class)->create();

        $firstLevelRightUser = factory(User::class)->create();

        $firstLevelRightUser->trees()->create(
            ['remain' => 1, 'capacity' => 1, 'progress' => '0']
        );

        $secondLevelLeftUser = factory(User::class)->create();

        $firstLevelLeftUser->appendNode($secondLevelLeftUser);

        $secondLevelLeftUser->trees()->create(
            ['remain' => 1, 'capacity' => 1, 'progress' => '0']
        );

        $secondLevelRightUser = factory(User::class)->create();
        $firstLevelLeftUser->appendNode($secondLevelRightUser);

        foreach ([
            ['remain' => 2, 'capacity' => 2, 'progress' => '0'],
            ['remain' => 2, 'capacity' => 2, 'progress' => '0'],
        ] as $tree) {
            $secondLevelRightUser->trees()->create($tree);
        }

        $rootUser->appendNode($firstLevelLeftUser);
        $rootUser->appendNode($firstLevelRightUser);

        foreach ([
            $secondLevelLeftUser, $secondLevelRightUser,
            $firstLevelLeftUser, $firstLevelRightUser,
        ] as $user) {
            /** @var TreeSettlement $job */
            $job = app(TreeSettlement::class, compact('user'));

            $job->handle();
        }

        $baseGemAmount = [];
        $gems = $this->gems(1);
        foreach (array_keys($gems) as $gem) {
            $baseGemAmount[$gem] = $rootUser->wallets()->save(
                factory(Wallet::class)->make(
                    [
                        'gem' => $gem,
                    ]
                )
            )->amount;
        }

        /** @var TreeSettlement $job */
        $job = app(TreeSettlement::class, ['user' => $rootUser]);

        $job->handle();

        $this->assertTreeSettlementHistoryExists(
            $rootUser,
            $testNow,
            [
                'progress_gained' => [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.2',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3124.0',
                ],
                'maximum_progress_rule' => [
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '6000.0',
                ],
            ]
        );

        $this->assertDatabaseHas(
            $trees[0]->getTable(),
            [
                'id' => $trees[0]->id,
                'user_id' => $trees[0]->user_id,
                'remain' => '0',
                'capacity' => 2,
                'progress' => '0.0',
            ]
        );

        foreach ($trees as $tree) {
            $this->assertOperationHistoryExists(
                $tree->refresh(),
                OperationHistory::TYPE_UPDATE
            );
        }

        $expectedGems = $this->gems(4);
        foreach ($expectedGems as $gem => $expectedGemAmount) {
            $this->assertDatabaseHas(
                (new Wallet)->getTable(),
                [
                    'user_id' => $rootUser->id,
                    'gem' => $gem,
                    'amount' => bcadd($baseGemAmount[$gem], $expectedGemAmount, 1),
                ]
            );

            if (bccomp($expectedGemAmount, '0', 1) !== 0) {
                $this->assertOperationHistoryExists(
                    Wallet::where(['user_id' => $rootUser->id, 'gem' => $gem])->firstOrFail(),
                    OperationHistory::TYPE_UPDATE
                );
            }
        }
    }

    public function dataProvider()
    {
        return [
            // original capacity, original progress, new capacity, new progress, Carbon test now, progress gained, maximum progress rule
            [
                1,
                '0',
                1,
                '14.3',
                'monday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '14.3',
                1,
                '28.6',
                'tuesday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '28.6',
                1,
                '42.9',
                'wednesday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '42.9',
                1,
                '57.2',
                'thursday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '57.2',
                1,
                '71.5',
                'friday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '71.5',
                1,
                '85.8',
                'saturday',
                $this->gems(0),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                1,
                '85.8',
                0,
                '0.0',
                'sunday',
                $this->gems(1),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.2',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                1,
                '99.0',
                0,
                '0.0',
                'saturday',
                $this->gems(1),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                2,
                '99.0',
                1,
                '13.3',
                'saturday',
                $this->gems(1),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                1,
                '750.0',
                0,
                '0.0',
                'saturday',
                $this->gems(1),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
            [
                8,
                '750.0',
                1,
                '64.3',
                'saturday',
                $this->gems(7),
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
        ];
    }

    public function settleNextDataProvider()
    {
        return [
            // day, gems, original, result, progress_gained, maximum_progress_rule
            [
                'saturday',
                $this->gems(2),
                [
                    ['capacity' => 1, 'progress' => '750.2'],
                    ['capacity' => 1, 'progress' => '0'],
                ],
                [
                    ['remain' => 0, 'progress' => '0'],
                    ['remain' => 0, 'progress' => '0'],
                ],
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                'sunday',
                $this->gems(7),
                [
                    ['capacity' => 8, 'progress' => '750.2'],
                    ['capacity' => 1, 'progress' => '0'],
                ],
                [
                    ['remain' => 1, 'progress' => '64.4'],
                    ['remain' => 1, 'progress' => '0'],
                ],
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.2',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                'sunday',
                $this->gems(9),
                [
                    ['capacity' => 8, 'progress' => '950.2'],
                    ['capacity' => 2, 'progress' => '0'],
                ],
                [
                    ['remain' => 0, 'progress' => '0'],
                    ['remain' => 1, 'progress' => '64.4'],
                ],
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.2',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                'monday',
                $this->gems(0),
                [
                    ['capacity' => 1, 'progress' => '50.2'],
                    ['capacity' => 2, 'progress' => '0'],
                ],
                [
                    ['remain' => 1, 'progress' => '64.5'],
                    ['remain' => 2, 'progress' => '0'],
                ],
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],

            [
                'thursday',
                $this->gems(3),
                [
                    ['capacity' => 1, 'progress' => '750.2'],
                    ['capacity' => 1, 'progress' => '0'],
                    ['capacity' => 1, 'progress' => '0'],
                ],
                [
                    ['remain' => 0, 'progress' => '0'],
                    ['remain' => 0, 'progress' => '0'],
                    ['remain' => 0, 'progress' => '0'],
                ],
                [
                    TreeSettlementHistory::KEY_SETTLEMENT_DAILY => '14.3',
                    TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '0',
                ],
                [TreeSettlementHistory::KEY_SETTLEMENT_DOWNLINES => '3000.0'],
            ],
        ];
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
}
