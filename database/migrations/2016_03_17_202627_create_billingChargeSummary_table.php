<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillingChargeSummaryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billingChargeSummary', function(Blueprint $table)
		{
			$table->integer('ID', true);
			$table->timestamp('DateTime')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->text('Period');
			$table->text('PeriodDescription');
			$table->text('Category');
			$table->text('SubCategory');
			$table->text('Description');
			$table->decimal('ApprovedNum', 10, 0);
			$table->decimal('ApprovedDollar', 10, 0);
			$table->decimal('DeclinedNum', 10, 0);
			$table->decimal('DeclinedDollar', 10, 0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('billingChargeSummary');
	}

}
