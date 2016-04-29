<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillingTransactionLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billingTransactionLog', function(Blueprint $table)
		{
			$table->bigInteger('LogID', true);
			$table->timestamp('DateTime')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->text('TransactionID');
			$table->text('Username');
			$table->text('CID')->nullable();
			$table->text('Name');
			$table->text('Amount');
			$table->text('TransType');
			$table->text('PaymentMode');
			$table->text('OrderNumber');
			$table->text('ChargeDescription');
			$table->text('ChargeDetails', 65535)->nullable();
			$table->text('ActionCode');
			$table->text('Approval');
			$table->text('Verification');
			$table->text('Responsetext');
			$table->text('Responseerror')->nullable();
			$table->text('Address');
			$table->text('Unit')->nullable();
			$table->text('Comment', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('billingTransactionLog');
	}

}
