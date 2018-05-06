<?php

use App\Events\DragonActivated;
use App\Events\WalletUpdated;
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        $user = \App\User::firstOrCreate(
            [
                'email' => 'root',
            ],
            [
                'name' => 'root',
                'password' => Hash::make('password'),
                'frozen' => false,
            ]
        );


        if (!$user->activated) {
            $dragon = $user->activatedDragon()->create(['owner_id' => $user->id]);
            event(new DragonActivated($dragon, $user));
        }

        foreach ((new Wallet)->gems() as $gem) {
            $wallet = $user->wallets()->firstOrCreate(
                [
                    'gem' => $gem,
                ], [
                    'amount' => '0',
                ]
            );

            event(new WalletUpdated($wallet));
        }

        DB::commit();
    }
}