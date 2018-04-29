<?php

namespace Tests\Feature;

use App\Jobs\FreezeUser;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * @dataProvider dataProvider
     * @param $scopes
     * @param $expectedStatusCode
     */
    public function testCreateChildAccount($scopes, $expectedStatusCode)
    {
        if ($expectedStatusCode === Response::HTTP_CREATED) {
            $this->expectsJobs(FreezeUser::class);
        }

        Passport::actingAs(
            $parent = factory(User::class)->create(),
            $scopes
        );

        $this
            ->json('POST', "/api/users/{$parent->id}/child-accounts")
            ->assertStatus($expectedStatusCode);

        if ($expectedStatusCode !== Response::HTTP_CREATED) {
            return;
        }

        $this->assertDatabaseHas(
            $parent->getTable(),
            [
                'user_id' => $parent->id,
            ]
        );

        $childAccount = $parent->childAccounts()->first();

        $this->assertParentHasChild($parent, $childAccount);
    }

    public function testItWillValidateUserPolicy()
    {
        $parent = factory(User::class)->create();

        Passport::actingAs(
            factory(User::class)->create(),
            ['create-child-accounts']
        );

        $this
            ->json('POST', "/api/users/{$parent->id}/child-accounts")
            ->assertStatus(403);

        $this->assertDatabaseMissing(
            $parent->getTable(),
            [
                'user_id' => $parent->id,
            ]
        );
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

    public function dataProvider()
    {
        return [
            [
                ['create-child-accounts'], 201,
            ],
            [
                [], 403,
            ],
        ];
    }
}
