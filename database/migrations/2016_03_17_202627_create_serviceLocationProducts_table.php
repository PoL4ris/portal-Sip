<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceLocationProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('serviceLocationProducts', function(Blueprint $table)
		{
			$table->bigInteger('SLPID', true);
			$table->bigInteger('LocID')->nullable();
			$table->bigInteger('ProdID')->nullable();
			$table->dateTime('ExpDate')->nullable();
			$table->text('Status')->nullable();
			$table->text('Comments', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('serviceLocationProducts');
	}

}
