<?php

namespace Tests\Feature;

use App\Jobs\FreezeUser;
use App\User;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ChildAccountTest extends TestCase
{
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
