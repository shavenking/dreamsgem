<?php

namespace Tests\Feature;

use App\Dragon;
use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BuyDragonTest extends TestCase
{
    use DatabaseTransactions;

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

        $this
            ->json('POST', "/api/users/{$user->id}/dragons")
            ->assertStatus($statusCode);

        if (Response::HTTP_CREATED === $statusCode) {
            $this->assertDragonExists($user);
            $this->assertOneTreeExists($user);
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
