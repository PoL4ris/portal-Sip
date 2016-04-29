<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCronLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cron_log', function(Blueprint $table)
		{
			$table->integer('ID', true);
			$table->text('Application');
			$table->timestamp('DateTime')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->text('Period');
			$table->text('Status');
			$table->text('NumResults');
			$table->text('Details', 65535);
			$table->text('Comment', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cron_log');
	}

}
