<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('payments', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('account_number')->comment('Account Number');
        $table->integer('exp_month')->comment('Exp Month');
        $table->integer('exp_year')->comment('Exp Year');
        $table->integer('id_types')->comment('Type');
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
      Schema::drop('payments');
    }
}
