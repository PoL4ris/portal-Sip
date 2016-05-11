<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrivilegeMenuElement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('privilege_menu_element', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_element');
        $table->integer('id_privilege_menu');
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
      Schema::drop('privilege_menu_element');
    }
}
