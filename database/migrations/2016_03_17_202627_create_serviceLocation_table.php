<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('serviceLocation', function(Blueprint $table)
		{
			$table->bigInteger('LocID', true);
			$table->text('Name');
			$table->text('ShortName')->nullable();
			$table->text('MgrName');
			$table->text('Address');
			$table->text('City');
			$table->text('State');
			$table->text('Zip');
			$table->text('Country');
			$table->text('MgmtTel');
			$table->text('MgmtFax');
			$table->text('MgmtEmail');
			$table->text('Type');
			$table->text('Units')->nullable();
			$table->text('ServiceType');
			$table->text('ContractExpire');
			$table->text('Comments', 65535);
			$table->text('MgrCompany');
			$table->text('Ethernet', 65535);
			$table->text('Wireless', 65535);
			$table->text('Speeds', 65535);
			$table->text('Billing', 65535);
			$table->text('EmailService', 65535);
			$table->text('IP');
			$table->text('DNS');
			$table->text('Gateway');
			$table->text('Other', 65535);
			$table->text('StaticIP', 65535);
			$table->text('RelocateEther', 65535);
			$table->text('DirectTV', 65535);
			$table->text('VoIP', 65535);
			$table->text('HowToConnect', 65535);
			$table->text('Description', 65535);
			$table->text('SupportNumber');
			$table->text('fnImage');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('serviceLocation');
	}

}
