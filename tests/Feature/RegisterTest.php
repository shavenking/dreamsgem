<?php

namespace Tests\Feature;

use App\Jobs\FreezeUser;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    public function testCreateRoot()
    {
        $this->expectsJobs(FreezeUser::class);

        $credentials = $this->makeCredentials();

        $this
            ->json('POST', '/api/users', $credentials)
            ->assertStatus(201);

        $this->assertUserExistsInDatabase($credentials);
    }

    public function testCreateChild()
    {
        $this->expectsJobs(FreezeUser::class);

        $credentials = $this->makeCredentials();

        $parentUser = factory(User::class)->create();

        $this
            ->json('POST', '/api/users', array_merge(
                $credentials, ['parent_id' => $parentUser->id]
            ))
            ->assertStatus(201);

        $this->assertUserExistsInDatabase($credentials);
        $this->assertParentHasChild($parentUser, $credentials);
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
