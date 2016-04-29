<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSalesPropertyInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('salesPropertyInfo', function(Blueprint $table)
		{
			$table->integer('SalesID', true);
			$table->text('ImageID')->nullable();
			$table->text('Name');
			$table->text('Nickname');
			$table->text('Code');
			$table->text('Type')->nullable();
			$table->text('Market')->nullable();
			$table->text('Street')->nullable();
			$table->text('City')->nullable();
			$table->text('State')->nullable();
			$table->text('Zip')->nullable();
			$table->text('Neighborhood')->nullable();
			$table->text('ShortName')->nullable();
			$table->text('ContactName')->nullable();
			$table->text('ContactPhone')->nullable();
			$table->text('ContactEmail')->nullable();
			$table->text('Webpage')->nullable();
			$table->text('MgmtCo')->nullable();
			$table->text('PropComments', 65535)->nullable();
			$table->text('BuiltDate')->nullable();
			$table->integer('Floors')->nullable();
			$table->integer('Units')->nullable();
			$table->text('Mgr_Name')->nullable();
			$table->text('Mgr_Tel')->nullable();
			$table->text('Mgr_Email')->nullable();
			$table->text('TV_Wiring')->nullable();
			$table->text('TV_Provider')->nullable();
			$table->text('TV_BulkRetail')->nullable();
			$table->text('TV_ContractExpire')->nullable();
			$table->text('TV_Details')->nullable();
			$table->text('TV_Price')->nullable();
			$table->text('INT_Wiring')->nullable();
			$table->text('INT_Provider')->nullable();
			$table->text('INT_BulkRetail')->nullable();
			$table->text('INT_UnderContract')->nullable();
			$table->text('INT_ContractExpire')->nullable();
			$table->text('INT_Details', 65535)->nullable();
			$table->text('INT_Price')->nullable();
			$table->text('Phone_Wiring')->nullable();
			$table->text('SalesComments', 65535)->nullable();
			$table->text('AccountRep')->nullable();
			$table->text('Priority')->nullable();
			$table->text('Status')->nullable();
			$table->text('Stage')->nullable();
			$table->text('Probability')->nullable();
			$table->text('Tags')->nullable();
			$table->dateTime('LastUpdate')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('salesPropertyInfo');
	}

}
