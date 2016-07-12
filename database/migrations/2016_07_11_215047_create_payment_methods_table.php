<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('payment_methods', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('account_number')->comment('Token');
        $table->text('properties')->comment('Properties');
        $table->integer('exp_month')->comment('Exp Month');
        $table->integer('exp_year')->comment('Exp Year');
        $table->string('types')->comment('Types');
        $table->integer('priority')->nullable();
        $table->integer('id_address')->comment('Address');
        $table->integer('id_customers')->comment('Customer');
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
      Schema::drop('payment_methods');
    }
}
