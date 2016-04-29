<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function(Blueprint $table)
		{
			$table->bigInteger('CID', true);
			$table->text('Firstname');
			$table->text('Lastname');
			$table->text('Username', 65535)->nullable();
			$table->text('Password', 65535)->nullable();
			$table->text('Company')->nullable();
			$table->text('LocID')->nullable();
			$table->text('PortID')->nullable();
			$table->text('VIP')->nullable();
			$table->text('Address');
			$table->text('Unit');
			$table->text('City');
			$table->text('State');
			$table->text('Zip');
			$table->text('Country')->nullable();
			$table->text('Tel');
			$table->text('Fax')->nullable();
			$table->text('Email', 65535);
			$table->text('SSN')->nullable();
			$table->text('CCtoken')->nullable();
			$table->text('CCnumber');
			$table->text('CCtype');
			$table->text('Expmo');
			$table->text('Expyr');
			$table->text('CCscode')->nullable();
			$table->timestamp('DateSignup')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('DateRenewed')->default('0000-00-00 00:00:00');
			$table->dateTime('DateExpires')->default('0000-00-00 00:00:00');
			$table->text('CustomerType')->nullable();
			$table->text('AccountStatus', 65535)->nullable();
			$table->text('Comments', 65535)->nullable();
			$table->string('Flag');
			$table->string('FlagNotice');
			$table->decimal('Deposit', 10)->nullable();
			$table->text('Modem')->nullable();
			$table->text('Wireless')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customers');
	}

}
