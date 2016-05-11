<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Apps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('apps', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_parent')->nullable();
        $table->string('name');
        $table->string('icon');
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
      Schema::drop('apps');
    }
}
