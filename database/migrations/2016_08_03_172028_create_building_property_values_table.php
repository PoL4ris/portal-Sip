<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingPropertyValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('building_property_values', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_buildings')->comment('Building');
        $table->integer('id_building_properties')->comment('Properties');
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
      Schema::drop('building_property_values');
    }
}
