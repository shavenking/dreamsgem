<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFourNewWalletToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = \Illuminate\Support\Facades\DB::table('users')->get(['id']);
        $gems = [5, 6, 7, 8];
        $now = \Illuminate\Support\Carbon::now();

        foreach ($users as $user) {
            foreach ($gems as $gem) {
                \Illuminate\Support\Facades\DB::table('wallets')->insert([
                    'user_id' => $user->id,
                    'gem' => $gem,
                    'amount' => '0.0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $gems = [5, 6, 7, 8];

        \Illuminate\Support\Facades\DB::table('wallets')->whereIn('gem', $gems)->delete();
    }
}
