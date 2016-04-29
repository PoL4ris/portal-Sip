<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('userImages', function(Blueprint $table)
		{
			$table->bigInteger('ImageID', true);
			$table->string('ImageType', 25);
			$table->binary('Image');
			$table->string('ImageSize', 25);
			$table->string('ImageCtgy', 25);
			$table->string('ImageName', 50);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('userImages');
	}

}
