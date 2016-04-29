<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceLocationGroupValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('serviceLocationGroupValues', function(Blueprint $table)
		{
			$table->integer('VID', true);
			$table->integer('LocID');
			$table->integer('GroupID');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('serviceLocationGroupValues');
	}

}
