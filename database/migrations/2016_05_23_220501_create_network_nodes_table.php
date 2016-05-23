<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('network_nodes', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('ip_address')->nullable()->comment('IP Address');
        $table->string('mac_address')->nullable()->comment('MAC Address');
        $table->string('host_name')->nullable()->comment('Host Name');
        $table->integer('id_address')->nullable()->comment('Address');
        $table->integer('id_types')->nullable()->comment('Type');
        $table->string('vendor')->nullable();
        $table->string('role')->nullable();
        $table->string('properties')->nullable();
        $table->string('comments')->nullable();
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
      Schema::drop('network_nodes');
    }
}
