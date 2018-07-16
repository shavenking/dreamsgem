<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecallSmallDragonReward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = \Illuminate\Support\Carbon::now();

        // find all small dragon owners
        $users = \Illuminate\Support\Facades\DB::table('users')
            ->leftJoin('dragons', 'dragons.user_id', '=', 'users.id')
            ->where('dragons.type', 1)
            ->get(['users.id']);

        // filter out owners that actually get rewards
        \Illuminate\Support\Facades\DB::beginTransaction();

        foreach ($users as $user) {
            $didReceiveReward = \Illuminate\Support\Facades\DB::table('operation_histories')
                ->where('user_id', $user->id)
                ->where('operatable_type', 'App\\Wallet')
                ->where('sub_type', 1)
                ->exists();

            if (!$didReceiveReward) {
                continue;
            }

            // recall rewards
            \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('gem', 3)
                ->decrement('amount', '100.0');

            $wallet = \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('gem', 3)
                ->first();

            \Illuminate\Support\Facades\DB::table('operation_histories')->insert([
                'operatable_type' => 'App\\Wallet',
                'operatable_id' => $wallet->id,
                'operator_id' => null,
                'user_id' => $user->id,
                'type' => 1, // TYPE_UPDATE
                'sub_type' => 12, // recall small dragon rewards
                'result_data' => json_encode([
                    'id' => $wallet->id,
                    'gem' => 3,
                    'amount' => $wallet->amount,
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]),
                'delta' => json_encode([
                    'amount' => '-100.0',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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
        // find all small dragon owners
        $users = \Illuminate\Support\Facades\DB::table('users')
            ->leftJoin('dragons', 'dragons.user_id', '=', 'users.id')
            ->where('dragons.type', 1)
            ->get(['users.id']);

        // filter out owners that actually get rewards
        \Illuminate\Support\Facades\DB::beginTransaction();

        foreach ($users as $user) {
            $didReceiveReward = \Illuminate\Support\Facades\DB::table('operation_histories')
                ->where('user_id', $user->id)
                ->where('operatable_type', 'App\\Wallet')
                ->where('sub_type', 1)
                ->exists();

            if (!$didReceiveReward) {
                continue;
            }

            // recall rewards
            \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('gem', 3)
                ->increment('amount', '100.0');

            $wallet = \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('gem', 3)
                ->first();

            \Illuminate\Support\Facades\DB::table('operation_histories')->where('operatable_type',
                'App\\Wallet')->where('operatable_id', $wallet->id)->where('type', 12)->delete();
        }

        \Illuminate\Support\Facades\DB::commit();
    }
}
