<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Menu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('menu', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_parent')->nullable();
        $table->string('name');
        $table->string('ico');
        $table->string('url');
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
      Schema::drop('menu');
    }
}
