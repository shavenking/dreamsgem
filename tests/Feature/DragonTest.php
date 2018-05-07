<?php

namespace Tests\Feature;

use App\Dragon;
use App\OperationHistory;
use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class DragonTest extends TestCase
{
    use RefreshDatabase, OperationHistoryAssertTrait;

    public function testGetFreeDragons()
    {
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        $freeDragons = factory(Dragon::class)->times(3)->create(['owner_id' => null, 'user_id' => null]);

        factory(Dragon::class)->create();

        $appUrl = env('APP_URL');
        $this
            ->json('GET', "/api/dragons")
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'current_page' => 1,
                    'data' => $freeDragons->toArray(),
                    'first_page_url' => "$appUrl/api/dragons?page=1",
                    'from' => 1,
                    'last_page' => 1,
                    'last_page_url' => "$appUrl/api/dragons?page=1",
                    'next_page_url' => null,
                    'path' => "$appUrl/api/dragons",
                    'per_page' => 15,
                    'prev_page_url' => null,
                    'to' => 3,
                    'total' => 3,
                ]
            );
    }

    /**
     * @dataProvider dataProvider
     * @param $scopes
     * @param $statusCode
     */
    public function testBuyDragon($scopes, $statusCode)
    {
        Passport::actingAs(
            $user = factory(User::class)->create(),
            $scopes
        );

        $dragon = factory(Dragon::class)->create(['owner_id' => null, 'user_id' => null]);

        $this
            ->json('PUT', "/api/dragons/{$dragon->id}", ['owner_id' => $user->id])
            ->assertStatus($statusCode);

        if (Response::HTTP_CREATED === $statusCode) {
            $this->assertDragonExists($user);
            $this->assertOperationHistoryExists(
                $user->dragons->first(),
                OperationHistory::TYPE_INITIAL,
                $user
            );
        }
    }

    public function testActivateDragon()
    {
        Passport::actingAs($dragonOwner = factory(User::class)->create());
        $dragon = $dragonOwner->dragons()->create();
        $dragonOwner->appendNode(
            $targetUserUpline = factory(User::class)->create()
        );

        $targetUserUplineWallet = $targetUserUpline->wallets()->save(
            factory(Wallet::class)->make(['gem' => Wallet::GEM_DUO_CAI])
        );

        $targetUserUpline->appendNode(
            $targetUser = factory(User::class)->create()
        );

        $this
            ->json('PUT', "/api/dragons/{$dragon->id}", [
                'user_id' => $targetUser->id,
            ])
            ->assertStatus(200);

        $this->assertDragonExists($dragonOwner, $targetUser);
        $this->assertOneTreeExists($targetUser, $targetUser);
        $this->assertOperationHistoryExists(
            $dragon,
            OperationHistory::TYPE_ACTIVATE,
            $dragonOwner
        );

        $this->assertDatabaseHas(
            (new Wallet)->getTable(),
            [
                'id' => $targetUserUplineWallet->id,
                'amount' => bcadd($targetUserUplineWallet->amount, '50', 1),
            ]
        );
    }

    public function testItWillValidateUserIsDownlines()
    {
        $root = factory(User::class)->create();

        $root->appendNode(
            $leftChild = factory(User::class)->create()
        );

        $root->appendNode(
            $rightChild = factory(User::class)->create()
        );

        $dragon = factory(Dragon::class)->create([
            'owner_id' => $leftChild->id,
            'user_id' => null,
        ]);

        Passport::actingAs($leftChild);

        $this->json('PUT', "/api/dragons/{$dragon->id}", [
            'user_id' => $rightChild->id,
        ])->assertStatus(400);
    }

    public function testItWillValidateIfDragonAlreadyActivate()
    {
        Passport::actingAs(
            $dragonOwner = factory(User::class)->create()
        );

        $dragonOwner->appendNode(
            $targetUser = factory(User::class)->create()
        );

        $dragon = factory(Dragon::class)->create([
            'owner_id' => $dragonOwner->id,
            'user_id' => $dragonOwner->id,
        ]);

        $this->json('PUT', "/api/dragons/{$dragon->id}", [
            'user_id' => $targetUser->id,
        ])->assertStatus(400);
    }

    public function testItWillValidateUserPolicy()
    {
        $user = factory(User::class)->create();
        Passport::actingAs(
            factory(User::class)->create()
        );
        $dragon = factory(Dragon::class)->create();

        $this
            ->json('PUT', "/api/dragons/{$dragon->id}", [
                'user_id' => $user->id,
            ])
            ->assertStatus(403);
    }

    private function assertDragonExists(User $user, User $targetUser = null)
    {
        $this->assertDatabaseHas(
            (new Dragon)->getTable(),
            [
                'owner_id' => $user->id,
                'user_id' => optional($targetUser)->id,
            ]
        );
    }

    private function assertOneTreeExists(User $user, User $activateUser)
    {
        $this->assertDatabaseHas(
            (new Tree)->getTable(),
            [
                'owner_id' => $user->id,
                'user_id' => optional($activateUser)->id,
                'progress' => '0',
                'remain' => 90,
                'capacity' => 90,
            ]
        );

        $this->assertCount(1, $user->trees, 'User can have only one tree at this moment.');
    }

    public function dataProvider()
    {
        return [
            'Valid Token' => [['create-dragons'], Response::HTTP_OK],
        ];
    }
}
