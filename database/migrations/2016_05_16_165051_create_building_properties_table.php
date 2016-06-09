<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBuildingPropertiesTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('building_properties', function(Blueprint $table)
    {
      $table->increments('id');
      $table->integer('id_buildings')->comment('-Building');
      $table->string('dedicated_number')->comment('-Dedicated Number');
      $table->text('wifi_info')->comment('-WiFi Info');
      $table->string('ip_range')->comment('-IP Range');
      $table->string('dns');
      $table->string('gateway');
      $table->text('comments');
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
    Schema::drop('building_properties');
  }

}
