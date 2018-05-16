<?php

namespace Tests\Feature;

use App\Dragon;
use App\Jobs\FreezeUser;
use App\OperationHistory;
use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class ChildAccountTest extends TestCase
{
    use RefreshDatabase, OperationHistoryAssertTrait, WithFaker;

    public function testGetChildAccounts()
    {
        /** @var User $user */
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        factory(User::class)->times(2)->create([
            'user_id' => $user->id,
        ])->map(function ($childAccount) {
            return [
                'id' => $childAccount->id,
                'name' => $childAccount->name,
                'email' => $childAccount->email,
                'frozen' => $childAccount->frozen,
                'created_at' => $childAccount->created_at->toDateTimeString(),
                'updated_at' => $childAccount->updated_at->toDateTimeString(),
                'is_child_account' => $childAccount->user_id !== null,
            ];
        });

        $childAccounts = User::whereUserId($user->id)->get();
        $appUrl = env('APP_URL');
        $this
            ->json('GET', "/api/users/{$user->id}/child-accounts")
            ->assertStatus(200)
            ->assertExactJson([
                'current_page' => 1,
                'data' => $childAccounts->toArray(),
                'first_page_url' => "$appUrl/api/users/{$user->id}/child-accounts?page=1",
                'from' => 1,
                'last_page' => 1,
                'last_page_url' => "$appUrl/api/users/{$user->id}/child-accounts?page=1",
                'next_page_url' => null,
                'path' => "$appUrl/api/users/{$user->id}/child-accounts",
                'per_page' => 15,
                'prev_page_url' => null,
                'to' => 2,
                'total' => 2,
            ]);
    }

    /**
     * @dataProvider dataProvider
     * @param $scopes
     * @param $expectedStatusCode
     */
    public function testCreateChildAccount($scopes, $expectedStatusCode)
    {
        if ($expectedStatusCode === Response::HTTP_CREATED) {
            $this->expectsJobs(FreezeUser::class);
        }

        Passport::actingAs(
            $parent = factory(User::class)->create()
        );

        factory(Dragon::class)->create(['user_id' => $parent->id]);

        $this
            ->json('POST', "/api/users/{$parent->id}/child-accounts")
            ->assertStatus($expectedStatusCode);

        if ($expectedStatusCode !== Response::HTTP_CREATED) {
            return;
        }

        $this->assertDatabaseHas(
            $parent->getTable(),
            [
                'user_id' => $parent->id,
            ]
        );

        $childAccount = $parent->childAccounts()->first();

        $this->assertParentHasChild($parent, $childAccount);
        $this->assertOperationHistoryExists(
            $childAccount,
            OperationHistory::TYPE_INITIAL
        );
    }

    public function testItWillValidateUserPolicy()
    {
        $parent = factory(User::class)->create();

        Passport::actingAs(
            factory(User::class)->create(),
            ['create-child-accounts']
        );

        $this
            ->json('POST', "/api/users/{$parent->id}/child-accounts")
            ->assertStatus(403);

        $this->assertDatabaseMissing(
            $parent->getTable(),
            [
                'user_id' => $parent->id,
            ]
        );
    }

    /**
     * 測試子母帳號一鍵召回（寶石）
     *
     * @dataProvider dataProviderForRecall
     */
    public function testRecall($zeroGained)
    {
        // 建立兩個母帳號
        list($user1, $user2) = factory(User::class)->times(2)->create();

        // 分別為母帳號建立子帳號
        list($user11, $user12) = factory(User::class)->times(2)->make()
            ->map(function (User $user) use ($user1) {
                return $user1->childAccounts()->save(
                    $user
                );
            });

        list($user21, $user22) = factory(User::class)->times(2)->make()
            ->map(function (User $user) use ($user2) {
                return $user2->childAccounts()->save(
                    $user
                );
            });

        // 為所有帳號建立錢包
        $gems = (new Wallet)->gems();

        foreach ($gems as $gem) {
            foreach (
                [
                    $user1, $user11, $user12,
                    $user2, $user21, $user22,
                ]
                as $user
            ) {
                $user->wallets()->create(
                    [
                        'gem' => $gem,
                        'amount' => $zeroGained ? '0.0' : "{$this->faker->numberBetween(0, 10000)}.{$this->faker->numberBetween(0, 9)}"
                    ]
                );
            }
        }

        // 測試召回其中一個母帳號的子帳號寶石
        Passport::actingAs($user1);

        $this
            ->json('POST', "/api/users/{$user1->id}/recalls")
            ->assertStatus(Response::HTTP_CREATED);

        // 驗證成功召回
        $walletTable = (new Wallet)->getTable();

        foreach ($user1->childAccounts as $childAccount) {
            foreach ($gems as $gem) {
                $this->assertDatabaseHas(
                    $walletTable,
                    [
                        'user_id' => $childAccount->id,
                        'gem' => $gem,
                        'amount' => '0.0'
                    ]
                );
            }
        }

        foreach ($gems as $gem) {
            $wallet = $user1->wallets->first(function ($wallet) use ($gem) {
                return $wallet->gem === $gem;
            });

            $user11Amount = $user11->wallets->first(function ($wallet) use ($gem) {
                return $wallet->gem === $gem;
            })->amount;
            $user12Amount = $user12->wallets->first(function ($wallet) use ($gem) {
                return $wallet->gem === $gem;
            })->amount;
            $totalAmountGained = bcadd($user11Amount, $user12Amount, 1);

            $this->assertDatabaseHas(
                $walletTable,
                [
                    'user_id' => $user1->id,
                    'gem' => $gem,
                    'amount' => bcadd(
                        $wallet->amount,
                        $totalAmountGained,
                        1
                    )
                ]
            );
        }

        // 驗證另一個母帳號的子帳號沒有被召回
        foreach ($user2->childAccounts as $childAccount) {
            foreach ($gems as $gem) {
                $wallet = $childAccount->wallets->first(function ($wallet) use ($gem) {
                    return $wallet->gem === $gem;
                });

                $this->assertDatabaseHas(
                    $walletTable,
                    [
                        'user_id' => $childAccount->id,
                        'gem' => $gem,
                        'amount' => $wallet->amount,
                    ]
                );
            }
        }

        // 驗證資料庫有記錄操作記錄
        foreach (
            [$user1, $user11, $user12]
            as $user
        ) {
            /** @var Wallet $wallet */
            foreach ($user->wallets as $wallet) {
                if ($zeroGained) {
                    $this->assertOperationHistoryNotExists(
                        $wallet->refresh(),
                        OperationHistory::TYPE_RECALL,
                        $user1
                    );
                } else {
                    $this->assertOperationHistoryExists(
                        $wallet->refresh(),
                        OperationHistory::TYPE_RECALL,
                        $user1
                    );
                }
            }
        }
    }

    private function assertParentHasChild($parentUser, $credentials)
    {
        $email = data_get($credentials, 'email');

        $this->assertTrue(
            optional(User::whereEmail($email)->first())->isChildOf($parentUser),
            "Parent child relation not match."
        );
    }

    public function dataProvider()
    {
        return [
            [
                ['create-child-accounts'], 201,
            ],
            [
                [], 201,
            ],
        ];
    }

    public function dataProviderForRecall()
    {
        return [
            [true],
            [false],
        ];
    }
}
