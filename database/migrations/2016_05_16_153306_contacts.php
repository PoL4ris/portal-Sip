<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Contacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('contacts', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_customers')->nullable()->comment("Customer");
        $table->integer('id_types')->nullable()->comment("Type-Type");
        $table->string('value');
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
      Schema::drop('contacts');
    }
}
