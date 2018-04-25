<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegisterTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    public function testCreateRoot()
    {
        $credentials = [
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ];

        $this
            ->json('POST', '/api/users', $credentials)
            ->assertStatus(201);

        $this->assertUserInDatabase($credentials);
    }

    private function assertUserInDatabase($credentials)
    {
        $user = User::whereEmail(data_get($credentials, 'email'))->first();

        $this->assertNotNull($user, 'User email not exists in database.');

        $this->assertTrue(Hash::check(
            data_get($credentials, 'password'),
            $user->password
        ), 'User password not matched.');
    }
}
