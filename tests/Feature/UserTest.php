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

    public function testGetUser()
    {
        /** @var User $user */
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        /** @var User $downlines */
        $downlines = factory(User::class)->times(7)->create()->each(function ($child) use ($user) {
            $user->appendNode($child);
        });

        $downlines->first()->appendNode(
            factory(User::class)->create()
        );

        $downlines = $downlines->map(function ($downline) {
            return [
                'id' => $downline->id,
                'name' => $downline->name,
                'email' => $downline->email,
                'frozen' => $downline->frozen,
                'created_at' => $downline->created_at->toDateTimeString(),
                'updated_at' => $downline->updated_at->toDateTimeString(),
                'is_child_account' => $downline->user_id !== null,
            ];
        });

        $this
            ->json('GET', "/api/users/{$user->id}")
            ->assertStatus(200)
            ->assertExactJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'frozen' => $user->frozen,
                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
                'is_child_account' => $user->user_id !== null,
                'downlines' => $downlines->toArray(),
            ]);
    }

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
