<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCapacityAndRemainColumnTypeOfTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->unsignedSmallInteger('remain')->change();
            $table->unsignedSmallInteger('capacity')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // doctrine does not support tiny integer
        // so we don't have rollback plan
    }
}
