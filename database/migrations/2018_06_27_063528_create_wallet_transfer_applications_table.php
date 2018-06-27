<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletTransferApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transfer_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_wallet_id')->index();
            $table->unsignedInteger('to_wallet_id')->index();
            $table->unsignedTinyInteger('status')->index();
            $table->decimal('rate');
            $table->decimal('amount', 20, 1);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transfer_applications');
    }
}
