<?php

namespace Tests\Feature;

use App\OperationHistory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker, OperationHistoryAssertTrait;

    public function testUpdateUserProfile()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        Passport::actingAs($user, ['update-profile']);

        $this
            ->json('PUT', "/api/users/{$user->id}", [
                'name' => $updatedName = $this->faker->name,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas(
            $user->getTable(),
            [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'name' => $updatedName,
                'email' => $user->email,
                'password' => $user->password,
                'frozen' => $user->frozen,
            ]
        );
        $this->assertOperationHistoryExists(
            $user->refresh(),
            OperationHistory::TYPE_UPDATE,
            $user
        );
    }

    public function testItWillValidateUserPolicy()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        Passport::actingAs(factory(User::class)->create(), ['update-profile']);

        $this
            ->json('PUT', "/api/users/{$user->id}", [
                'name' => $updatedName = $this->faker->name,
            ])
            ->assertStatus(403);
    }
}
