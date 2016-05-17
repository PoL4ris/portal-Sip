<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessAppElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('access_app_elements', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_elements')->comment("Element");
        $table->integer('id_access_apps')->comment("Access");
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
      Schema::drop('access_app_elements');
    }
}
