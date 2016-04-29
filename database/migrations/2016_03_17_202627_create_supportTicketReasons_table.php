<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSupportTicketReasonsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supportTicketReasons', function(Blueprint $table)
		{
			$table->integer('RID', true);
			$table->string('ReasonName');
			$table->text('ReasonShortDesc');
			$table->text('ReasonCategory');
			$table->text('ReasonDescription');
			$table->text('WirelessReasonDescription');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supportTicketReasons');
	}

}
