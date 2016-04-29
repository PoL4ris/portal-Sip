<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSalesActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('salesActivity', function(Blueprint $table)
		{
			$table->integer('SalesID');
			$table->integer('SalesActivityID', true);
			$table->dateTime('ActivityDateTime');
			$table->text('ActivityType');
			$table->text('ActivityDetails');
			$table->text('ActivityAttachments');
			$table->text('ActivityFollowup', 65535)->nullable();
			$table->dateTime('ActivityFollowupDate');
			$table->dateTime('CreatedOn')->nullable();
			$table->text('CreatedBy')->nullable();
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
		Schema::drop('salesActivity');
	}

}
