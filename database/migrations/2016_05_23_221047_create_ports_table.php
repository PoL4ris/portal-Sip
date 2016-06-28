<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ports', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('port_number')->nullable()->comment('Port Number');
        $table->string('access_level')->nullable()->comment('Access Level');
        $table->integer('id_customer_products')->nullable()->comment('Product');
        $table->integer('id_customers')->nullable()->comment('Customer');
        $table->integer('id_network_nodes')->nullable()->comment('Network Node');
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
      Schema::drop('ports');
    }
}
