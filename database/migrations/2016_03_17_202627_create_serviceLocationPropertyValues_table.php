<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceLocationPropertyValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('serviceLocationPropertyValues', function(Blueprint $table)
		{
			$table->integer('VID', true);
			$table->integer('LocID');
			$table->integer('PropID');
			$table->text('Value');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('serviceLocationPropertyValues');
	}

}
