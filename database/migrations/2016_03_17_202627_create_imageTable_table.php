<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImageTableTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('imageTable', function(Blueprint $table)
		{
			$table->boolean('image_id')->index('image_id');
			$table->string('image_type', 25);
			$table->binary('image');
			$table->string('image_size', 25);
			$table->string('image_ctgy', 25);
			$table->string('image_name', 50);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('imageTable');
	}

}
