<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class BuildingContacts extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('building_contacts', function(Blueprint $table)
    {
      $table->increments('id');
      $table->integer('id_buildings')->comment('-Building');
      $table->string('first_name')->comment('-First Name');
      $table->string('last_name')->comment('-Last Name');
      $table->string('contact');
      $table->string('fax');
      $table->string('company');
      $table->string('comments');
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
    Schema::drop('building_contacts');
  }

}
