<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerPortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_port', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('port_id');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::drop('customer_port');
    }
}
