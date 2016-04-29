<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTablePolarisTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_table_polaris', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('locid')->unsigned();
			$table->string('shortname');
			$table->string('month');
			$table->string('year');
			$table->text('revenue_data', 65535);
			$table->string('properties')->nullable();
			$table->string('status');
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
		Schema::drop('test_table_polaris');
	}

}
