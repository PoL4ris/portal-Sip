<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetailRevenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retail_revenues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('locid',false,true);
            $table->string('shortname');
            $table->string('month');
            $table->string('year');
            $table->text('revenue_data');
            $table->string('properties')->nullable();
            $table->string('status');
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
        Schema::drop('retail_revenues');
    }
}
