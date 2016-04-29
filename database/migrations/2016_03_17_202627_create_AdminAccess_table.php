<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('AdminAccess', function(Blueprint $table)
		{
			$table->integer('ID', true);
			$table->text('Image');
			$table->text('Name');
			$table->text('Nickname');
			$table->text('Email')->nullable();
			$table->text('Company');
			$table->text('Username');
			$table->text('Password');
			$table->text('Comments', 65535);
			$table->text('Access');
			$table->integer('AccessLevel')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('AdminAccess');
	}

}
