<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBuildingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('building', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('img_building', 65535);
			$table->string('name');
			$table->string('alias');
			$table->string('nickname');
			$table->string('address');
			$table->integer('id_neighborhood');
			$table->string('code');
			$table->string('type');
			$table->string('legal_name');
			$table->string('builder');
			$table->date('year_built');
			$table->integer('units');
			$table->integer('floors');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('building');
	}

}
