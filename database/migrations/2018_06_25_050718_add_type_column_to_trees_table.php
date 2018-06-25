<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->after('id')->index();
        });

        // 目前所有樹種皆為「小樹」
        DB::table('trees')->update(['type' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
