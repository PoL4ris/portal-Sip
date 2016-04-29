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
			$table->integer('id_building');
			$table->string('dedicate_number');
			$table->string('wifi_info');
			$table->string('ip_range');
			$table->string('dns');
			$table->string('gateway');
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
		Schema::drop('building_properties');
	}

}
