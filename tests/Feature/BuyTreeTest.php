<?php

namespace Tests\Feature;

use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BuyTreeTest extends TestCase
{
    use DatabaseTransactions;

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
                    'user_id' => $user->id,
                    'capacity' => 90,
                    'progress' => '0',
                ]
            );
        }
    }

    public function dataProvider()
    {
        return [
            'Valid Token' => [['create-trees'], Response::HTTP_CREATED],
            'Invalid Token' => [[''], Response::HTTP_FORBIDDEN],
        ];
    }
}
