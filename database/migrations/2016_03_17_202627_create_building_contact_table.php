<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBuildingContactTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('building_contact', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('id_building');
			$table->string('first_name');
			$table->string('last_name');
			$table->string('contact');
			$table->string('fax');
			$table->string('company');
			$table->string('comments');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('building_contact');
	}

}
