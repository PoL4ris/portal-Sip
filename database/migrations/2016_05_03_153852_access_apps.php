<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccessApps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('access_apps', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_apps')->comment("App");
        $table->integer('id_profiles')->comment("Profile");
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
      Schema::drop('access_apps');
    }
}
