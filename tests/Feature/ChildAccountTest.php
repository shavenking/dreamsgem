<?php

namespace Tests\Feature;

use App\Dragon;
use App\Jobs\FreezeUser;
use App\OperationHistory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;
use Tests\OperationHistoryAssertTrait;
use Tests\TestCase;

class ChildAccountTest extends TestCase
{
    use RefreshDatabase, OperationHistoryAssertTrait;

    public function testGetChildAccounts()
    {
        /** @var User $user */
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        factory(User::class)->times(2)->create([
            'user_id' => $user->id,
        ])->map(function ($childAccount) {
            return [
                'id' => $childAccount->id,
                'name' => $childAccount->name,
                'email' => $childAccount->email,
                'frozen' => $childAccount->frozen,
                'created_at' => $childAccount->created_at->toDateTimeString(),
                'updated_at' => $childAccount->updated_at->toDateTimeString(),
                'is_child_account' => $childAccount->user_id !== null,
            ];
        });

        $childAccounts = User::whereUserId($user->id)->get();
        $appUrl = env('APP_URL');
        $this
            ->json('GET', "/api/users/{$user->id}/child-accounts")
            ->assertStatus(200)
            ->assertExactJson([
                'current_page' => 1,
                'data' => $childAccounts->toArray(),
                'first_page_url' => "$appUrl/api/users/{$user->id}/child-accounts?page=1",
                'from' => 1,
                'last_page' => 1,
                'last_page_url' => "$appUrl/api/users/{$user->id}/child-accounts?page=1",
                'next_page_url' => null,
                'path' => "$appUrl/api/users/{$user->id}/child-accounts",
                'per_page' => 15,
                'prev_page_url' => null,
                'to' => 2,
                'total' => 2,
            ]);
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
            $parent = factory(User::class)->create()
        );

        factory(Dragon::class)->create(['user_id' => $parent->id]);

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
        $this->assertOperationHistoryExists(
            $childAccount,
            OperationHistory::TYPE_INITIAL
        );
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
                [], 201,
            ],
        ];
    }
}
