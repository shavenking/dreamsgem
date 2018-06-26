<?php

use App\Events\DragonActivated;
use App\Events\WalletCreated;
use App\Events\WalletUpdated;
use App\Wallet;
use Carbon\Carbon;
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
        if (\App\User::find(1)) {
            return;
        }

        DB::beginTransaction();

        $user = \App\User::firstOrCreate(
            [
                'email' => 'root@email.com',
            ],
            [
                'name' => 'root',
                'password' => Hash::make('password'),
                'wallet_password' => Hash::make('88888888'),
                'frozen' => false,
            ]
        );


        if (!$user->activated) {
            $dragon = $user->activatedDragon()->create(
                ['owner_id' => $user->id, 'activated_at' => Carbon::now()]
            );
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

            event(new WalletCreated($wallet));
        }

        DB::commit();
    }
}
