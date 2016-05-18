<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('reasons', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('name');
        $table->string('short_description')->comment('Short Desc');
        $table->integer('id_categories')->comment('Category');
        $table->text('description');
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop('reasons');
    }
}
