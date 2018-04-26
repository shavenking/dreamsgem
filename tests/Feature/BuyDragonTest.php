<?php

namespace Tests\Feature;

use App\Dragon;
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
            $this->assertDatabaseHas(
                (new Dragon)->getTable(),
                [
                    'user_id' => $user->id
                ]
            );
        }
    }

    public function dataProvider()
    {
        return [
            'Valid Token' => [['create-dragons'], Response::HTTP_CREATED],
            'Invalid Token' => [[''], Response::HTTP_FORBIDDEN],
        ];
    }
}
