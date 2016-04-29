<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductPropertyValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('productPropertyValues', function(Blueprint $table)
		{
			$table->integer('VID', true);
			$table->integer('ProdID');
			$table->integer('PropID');
			$table->text('Value');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('productPropertyValues');
	}

}
