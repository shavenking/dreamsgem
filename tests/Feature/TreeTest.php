<?php

namespace Tests\Feature;

use App\OperationHistory;
use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class TreeTest extends TestCase
{
    use RefreshDatabase, OperationHistoryAssertTrait;

    /**
     * @dataProvider dataProvider
     * @param $scopes
     * @param $statusCode
     */
    public function testBuyTree($scopes, $statusCode)
    {
        Passport::actingAs(
            $user = factory(User::class)->create(),
            $scopes
        );

        $this
            ->json('POST', "/api/users/{$user->id}/trees")
            ->assertStatus($statusCode);

        if (Response::HTTP_CREATED === $statusCode) {
            $this->assertDatabaseHas(
                (new Tree)->getTable(),
                [
                    'owner_id' => $user->id,
                    'user_id' => null,
                    'remain' => 90,
                    'capacity' => 90,
                    'progress' => '0',
                ]
            );

            $this->assertOperationHistoryExists(
                Tree::first(),
                OperationHistory::TYPE_INITIAL,
                $user
            );
        }
    }

    public function testActivateTree()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        /** @var Tree $tree */
        $tree = factory(Tree::class)->create(
            [
                'owner_id' => $user->id,
                'user_id' => null,
            ]
        );

        /** @var User $childAccount */
        $childAccount = factory(User::class)->create(
            [
                'user_id' => $user->id,
            ]
        );

        $this->json('PUT', "/api/users/{$user->id}/trees/{$tree->id}", [
            'user_id' => $childAccount->id,
        ])->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(
            $tree->getTable(),
            array_except(array_merge($tree->toArray(), [
                'user_id' => $childAccount->id,
            ]), ['created_at', 'updated_at'])
        );

        $this->assertOperationHistoryExists(
            $tree->refresh(),
            OperationHistory::TYPE_ACTIVATE,
            $user
        );
    }

    public function testItWillValidateUserIsChildAccount()
    {
        Passport::actingAs(
            $rootUser = factory(User::class)->create()
        );

        $tree = factory(Tree::class)->create([
            'owner_id' => $rootUser,
        ]);

        $rootUser->appendNode(
            $downline = factory(User::class)->create()
        );

        $downlineChildAccount = factory(User::class)->create([
            'user_id' => $downline,
        ]);

        $this->json('PUT', "/api/users/{$rootUser->id}/trees/{$tree->id}", [
            'user_id' => $downlineChildAccount->id,
        ])->assertStatus(400);
    }

    public function testItWillValidateIfDragonAlreadyActivate()
    {
        Passport::actingAs(
            $rootUser = factory(User::class)->create()
        );

        $tree = factory(Tree::class)->create([
            'owner_id' => $rootUser,
            'user_id' => $rootUser,
        ]);

        $rootUserChildAccount = factory(User::class)->create([
            'user_id' => $rootUser,
        ]);

        $this->json('PUT', "/api/users/{$rootUser->id}/trees/{$tree->id}", [
            'user_id' => $rootUserChildAccount->id,
        ])->assertStatus(400);
    }

    public function testItWillValidateUserPolicy()
    {
        $user = factory(User::class)->create();
        Passport::actingAs(
            factory(User::class)->create(),
            ['create-trees']
        );

        $this
            ->json('POST', "/api/users/{$user->id}/trees")
            ->assertStatus(403);
    }

    public function dataProvider()
    {
        return [
            'Valid Token' => [['create-trees'], Response::HTTP_CREATED],
        ];
    }
}
