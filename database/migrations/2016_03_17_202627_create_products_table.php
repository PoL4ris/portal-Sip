<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->bigInteger('ProdID', true);
			$table->text('ProdName');
			$table->text('ProdDescription');
			$table->text('ProdType');
			$table->text('Amount');
			$table->text('ChargeFrequency');
			$table->text('ContractTerm');
			$table->dateTime('ContractExpires')->default('0000-00-00 00:00:00');
			$table->text('AutoRenew');
			$table->text('ProdComments', 65535);
			$table->bigInteger('ParentProdID');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('products');
	}

}
