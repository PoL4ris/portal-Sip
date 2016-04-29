<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomerProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customerProducts', function(Blueprint $table)
		{
			$table->bigInteger('CSID', true);
			$table->text('CID');
			$table->text('ProdID');
			$table->text('Status');
			$table->integer('ParentCSID');
			$table->text('Comments', 65535);
			$table->timestamp('CProdDateSignup')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('CProdDateRenewed')->default('0000-00-00 00:00:00');
			$table->dateTime('CProdDateExpires')->default('0000-00-00 00:00:00');
			$table->dateTime('CProdDateUpdated')->default('0000-00-00 00:00:00');
			$table->dateTime('CProdLastCharged')->default('0000-00-00 00:00:00');
			$table->integer('UpdatedByID');
			$table->string('ServiceFlag')->default('0');
			$table->string('ServiceFlagNotice');
			$table->string('BillingFlag')->default('0');
			$table->string('BillingFlagNotice');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customerProducts');
	}

}
