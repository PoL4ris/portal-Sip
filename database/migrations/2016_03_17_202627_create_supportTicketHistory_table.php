<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSupportTicketHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supportTicketHistory', function(Blueprint $table)
		{
			$table->integer('THID', true);
			$table->integer('TID');
			$table->integer('RID');
			$table->text('Comment');
			$table->string('Status');
			$table->string('StaffID');
			$table->text('AssignedToID')->nullable();
			$table->timestamp('TimeStamp')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supportTicketHistory');
	}

}
