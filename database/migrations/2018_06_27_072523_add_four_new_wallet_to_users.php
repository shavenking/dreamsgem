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
        $now = \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s');

        \Illuminate\Support\Facades\DB::beginTransaction();

        foreach ($users as $user) {
            foreach ($gems as $gem) {
                $walletId = \Illuminate\Support\Facades\DB::table('wallets')->insertGetId([
                    'user_id' => $user->id,
                    'gem' => $gem,
                    'amount' => '0.0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                \Illuminate\Support\Facades\DB::table('operation_histories')->insert([
                    'operatable_type' => 'App\\Wallet',
                    'operatable_id' => $walletId,
                    'operator_id' => null,
                    'user_id' => $user->id,
                    'type' => 0,
                    'sub_type' => null,
                    'result_data' => json_encode([
                        'id' => $walletId,
                        'gem' => $gem,
                        'amount' => '0.0',
                        'user_id' => $user->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]),
                    'delta' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $gems = [5, 6, 7, 8];

        $walletIds = \Illuminate\Support\Facades\DB::table('wallets')->whereIn('gem', $gems)->pluck('id');

        \Illuminate\Support\Facades\DB::beginTransaction();

        \Illuminate\Support\Facades\DB::table('operation_histories')->where('operatable_type', 'App\\Wallet')->whereIn('operatable_id', $walletIds)->delete();

        \Illuminate\Support\Facades\DB::table('wallets')->whereIn('id', $walletIds)->delete();

        \Illuminate\Support\Facades\DB::commit();
    }
}
