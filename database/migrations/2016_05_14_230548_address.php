<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Address extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('address', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('address');
        $table->integer('unit');
        $table->string('city');
        $table->integer('zip');
        $table->string('state');
        $table->string('country')->nullable();
        $table->integer('id_customers')->nullable()->comment("-Customer");
        $table->integer('id_buildings')->nullable()->comment("-Building");
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
      Schema::drop('address');
    }
}
