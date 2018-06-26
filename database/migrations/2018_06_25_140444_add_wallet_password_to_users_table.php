<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWalletPasswordToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_password')->after('password');
        });

        $defaultPassword = \Illuminate\Support\Facades\Hash::make('88888888');
        \Illuminate\Support\Facades\DB::table('users')->update(['wallet_password' => $defaultPassword]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_password');
        });
    }
}
