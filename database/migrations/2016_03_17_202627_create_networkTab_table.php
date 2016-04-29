<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNetworkTabTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('networkTab', function(Blueprint $table)
		{
			$table->integer('NID', true);
			$table->text('location');
			$table->text('address');
			$table->text('core')->nullable();
			$table->text('dist')->nullable();
			$table->text('primary');
			$table->text('backup')->nullable();
			$table->text('mgmtnet');
			$table->integer('segment');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('networkTab');
	}

}
