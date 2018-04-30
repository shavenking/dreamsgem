<?php

namespace Tests\Feature;

use App\Jobs\TreeSettlement;
use App\OperationHistory;
use App\Tree;
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
     */
    public function testTreeSettlement(
        $originalCapacity,
        $originalProgress,
        $remain,
        $newProgress,
        $day,
        $gems
    ) {
        $this->setTestNow($day);

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
     */
    public function testItWillSettleNextTreeIfPossible(
        $day,
        $gems,
        $originalDataSet,
        $resultDataSet
    ) {
        $this->setTestNow($day);

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

    public function dataProvider()
    {
        return [
            // original capacity, original progress, new capacity, new progress, Carbon test now
            [1, '0', 1, '14.3', 'monday', $this->gems(0)],
            [1, '14.3', 1, '28.6', 'tuesday', $this->gems(0)],
            [1, '28.6', 1, '42.9', 'wednesday', $this->gems(0)],
            [1, '42.9', 1, '57.2', 'thursday', $this->gems(0)],
            [1, '57.2', 1, '71.5', 'friday', $this->gems(0)],
            [1, '71.5', 1, '85.8', 'saturday', $this->gems(0)],
            [1, '85.8', 0, '0.0', 'sunday', $this->gems(1)],

            [1, '99.0', 0, '0.0', 'saturday', $this->gems(1)],
            [2, '99.0', 1, '13.3', 'saturday', $this->gems(1)],

            [1, '750.0', 0, '0.0', 'saturday', $this->gems(1)],
            [8, '750.0', 1, '64.3', 'saturday', $this->gems(7)],
        ];
    }

    public function settleNextDataProvider()
    {
        return [
            // day, gems, original, result
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
            ],
        ];
    }

    private function setTestNow($day)
    {
        return Carbon::setTestNow(
            Carbon::createFromTimestamp(strtotime($day))
        );
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
}
