<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movement_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("cash_movement_id");
            $table->unsignedBigInteger("money_id");
            $table->foreign('cash_movement_id')->references('id')->on('cash_movements');
            $table->foreign('money_id')->references('id')->on('money');
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
        Schema::dropIfExists('movement_detail');
    }
}
