<?php

namespace Tests\Feature;

use App\Dragon;
use App\OperationHistory;
use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class BuyDragonTest extends TestCase
{
    use DatabaseTransactions, OperationHistoryAssertTrait;

    /**
     * @dataProvider dataProvider
     * @param $scopes
     * @param $statusCode
     */
    public function testBuyDragon($scopes, $statusCode)
    {
        $parentUser = factory(User::class)->create();
        $parentWallet = $parentUser->wallets()->save(
            factory(Wallet::class)->make(['gem' => Wallet::GEM_DUO_CAI])
        );

        Passport::actingAs(
            $user = factory(User::class)->create(),
            $scopes
        );

        $parentUser->appendNode($user);

        $this
            ->json('POST', "/api/users/{$user->id}/dragons")
            ->assertStatus($statusCode);

        if (Response::HTTP_CREATED === $statusCode) {
            $this->assertDragonExists($user);
            $this->assertOneTreeExists($user);
            $this->assertOperationHistoryExists(
                $user->dragon,
                OperationHistory::TYPE_INITIAL,
                $user
            );

            $this->assertDatabaseHas(
                (new Wallet)->getTable(),
                [
                    'id' => $parentWallet->id,
                    'amount' => bcadd($parentWallet->amount, '50', 1),
                ]
            );
        }
    }

    public function testItWillValidateUserPolicy()
    {
        $user = factory(User::class)->create();
        Passport::actingAs(
            factory(User::class)->create(),
            ['create-dragons']
        );

        $this
            ->json('POST', "/api/users/{$user->id}/dragons")
            ->assertStatus(403);
    }

    private function assertDragonExists(User $user)
    {
        $this->assertDatabaseHas(
            (new Dragon)->getTable(),
            [
                'user_id' => $user->id
            ]
        );
    }

    private function assertOneTreeExists(User $user)
    {
        $this->assertDatabaseHas(
            (new Tree)->getTable(),
            [
                'user_id' => $user->id
            ]
        );

        $this->assertCount(1, $user->trees, 'User can have only one tree at this moment.');
    }

    public function dataProvider()
    {
        return [
            'Valid Token' => [['create-dragons'], Response::HTTP_CREATED],
            'Invalid Token' => [[''], Response::HTTP_FORBIDDEN],
        ];
    }
}
