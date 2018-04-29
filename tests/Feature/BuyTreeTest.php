<?php

namespace Tests\Feature;

use App\OperationHistory;
use App\Tree;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class BuyTreeTest extends TestCase
{
    use DatabaseTransactions, OperationHistoryAssertTrait;

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
            $this->assertOperationHistoryExists(
                Tree::first(),
                OperationHistory::TYPE_INITIAL,
                $user
            );
        }
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
            'Invalid Token' => [[''], Response::HTTP_FORBIDDEN],
        ];
    }
}
