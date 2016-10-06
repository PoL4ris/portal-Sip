<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataShapesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('data_shapes', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('action')->nullable();
        $table->string('type')->nullable();
        $table->string('host_name')->nullable();
        $table->string('ip')->nullable();
        $table->string('interface')->nullable();
        $table->string('vlan')->nullable();
        $table->string('switch')->nullable();
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
      Schema::drop('data_shapes');
    }
}
