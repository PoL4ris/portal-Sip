<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSalesPropertyRemindersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('salesPropertyReminders', function(Blueprint $table)
		{
			$table->integer('ReminderID', true);
			$table->integer('SalesID');
			$table->text('Type')->nullable();
			$table->text('Description', 65535)->nullable();
			$table->text('Status')->nullable();
			$table->text('Priority')->nullable();
			$table->text('Tags')->nullable();
			$table->dateTime('CreatedOn')->nullable();
			$table->text('CreatedBy')->nullable();
			$table->dateTime('DueDate')->nullable();
			$table->dateTime('LastUpdate')->nullable();
			$table->text('LastUpdatedBy')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('salesPropertyReminders');
	}

}
