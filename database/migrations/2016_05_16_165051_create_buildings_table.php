<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBuildingsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('buildings', function(Blueprint $table)
    {
      $table->increments('id');
      $table->text('img_building', 65535)->nullable();
      $table->string('name');
      $table->string('alias');
      $table->string('nickname');
      $table->integer('id_neighborhoods')->comment('Neighborhood');
      $table->string('code');
      $table->string('type')->comment('Type');
      $table->string('legal_name')->comment('Legal name');
      $table->string('builder');
      $table->timestamp('year_built')->comment('Year Built');
      $table->integer('units');
      $table->integer('floors');
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
    Schema::drop('buildings');
  }

}
