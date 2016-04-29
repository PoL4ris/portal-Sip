<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateACHBulkTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ACHBulk', function(Blueprint $table)
		{
			$table->integer('ID', true);
			$table->text('Name');
			$table->text('Amount');
			$table->text('ABA');
			$table->text('AccountNum');
			$table->integer('CheckNum');
			$table->text('AccountType');
			$table->text('SEC');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ACHBulk');
	}

}
