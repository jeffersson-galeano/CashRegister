<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("log_id");
            $table->unsignedBigInteger("cash_money_id");
            $table->foreign('log_id')->references('id')->on('logs');
            $table->foreign('cash_money_id')->references('id')->on('cash_money');
            $table->integer("amount");
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
        Schema::dropIfExists('log_details');
    }
}
