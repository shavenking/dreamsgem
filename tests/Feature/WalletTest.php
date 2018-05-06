<?php

namespace Tests\Feature;

use App\User;
use App\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function testListWallets()
    {
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        foreach ((new Wallet)->gems() as $gem) {
            factory(Wallet::class)->create(
                [
                    'user_id' => $user->id,
                    'gem' => $gem,
                ]
            );
        }

        $wallets = $user->wallets;
        $appUrl = env('APP_URL');
        $this
            ->json('GET', "/api/users/{$user->id}/wallets")
            ->assertStatus(200)
            ->assertExactJson(
                [
                    'current_page' => 1,
                    'data' => $wallets->toArray(),
                    'first_page_url' => "$appUrl/api/users/{$user->id}/wallets?page=1",
                    'from' => 1,
                    'last_page' => 1,
                    'last_page_url' => "$appUrl/api/users/{$user->id}/wallets?page=1",
                    'next_page_url' => null,
                    'path' => "$appUrl/api/users/{$user->id}/wallets",
                    'per_page' => 15,
                    'prev_page_url' => null,
                    'to' => 4,
                    'total' => 4,
                ]
            );
    }
}
