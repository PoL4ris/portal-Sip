<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSupportTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supportTickets', function(Blueprint $table)
		{
			$table->integer('TID', true);
			$table->integer('CID');
			$table->string('TicketNumber');
			$table->text('VendorTID')->nullable();
			$table->integer('RID');
			$table->text('Comment');
			$table->string('Status');
			$table->text('StaffID');
			$table->text('AssignedToID')->nullable();
			$table->dateTime('DateCreated')->default('0000-00-00 00:00:00');
			$table->timestamp('LastUpdate')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supportTickets');
	}

}
