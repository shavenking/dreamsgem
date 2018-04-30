<?php

namespace Tests\Feature;

use App\Jobs\FreezeUser;
use App\OperationHistory;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use WithFaker, DatabaseTransactions, OperationHistoryAssertTrait;

    public function testCreateChild()
    {
        $this->expectsJobs(FreezeUser::class);

        /** @var User $parent */
        $parent = factory(User::class)->create();

        /** @var User $targetUser */
        $targetUser = factory(User::class)->times(7)->create()->each(function ($user) use ($parent) {
            $parent->appendNode($user);
        })->first();

        $targetUser->appendNode(
            factory(User::class)->create()
        );

        $credentials = $this->makeCredentials();

        $this
            ->json('POST', '/api/users', array_merge(
                $credentials, ['parent_id' => $parent->id]
            ))
            ->assertStatus(201);

        $this->assertUserExistsInDatabase($credentials);
        $this->assertParentHasChild($targetUser, $credentials);
        $this->assertOperationHistoryExists(
            User::whereEmail($credentials['email'])->firstOrFail(),
            OperationHistory::TYPE_INITIAL
        );
    }

    public function testUserCannotCreateWithoutParent()
    {
        $credentials = $this->makeCredentials();

        $this
            ->json('POST', '/api/users', $credentials)
            ->assertStatus(422);
    }

    private function makeCredentials(): array
    {
        return [
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ];
    }

    private function assertUserExistsInDatabase(array $filters)
    {
        $user = User::where(array_except($filters, 'password'))->first();

        $this->assertNotNull($user, 'User email not exists in database.');

        $this->assertTrue(Hash::check(
            data_get($filters, 'password'),
            $user->password
        ), 'User password not matched.');
    }

    private function assertParentHasChild($parentUser, $credentials)
    {
        $email = data_get($credentials, 'email');

        $this->assertTrue(
            optional(User::whereEmail($email)->first())->isChildOf($parentUser),
            "Parent child relation not match."
        );
    }
}
