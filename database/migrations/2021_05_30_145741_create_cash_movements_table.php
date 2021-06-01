<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("cash_id");
            $table->unsignedBigInteger("movement_type_id");
            $table->foreign('cash_id')->references('id')->on('cash');
            $table->foreign('movement_type_id')->references('id')->on('movement_type');
            $table->bigInteger("in_value");
            $table->bigInteger("out_value");
            $table->bigInteger("net_income");
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
        Schema::dropIfExists('cash_movements');
    }
}
