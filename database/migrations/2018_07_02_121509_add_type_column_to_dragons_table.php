<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToDragonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dragons', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->after('id')->index();
        });

        // 目前所有龍皆為預設的龍
        DB::table('dragons')->update(['type' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dragons', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
