<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('terms', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_products')->comment('Product');
        $table->string('length');
        $table->date('expires_at')->comment('Expires at');
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
      Schema::drop('terms');
    }
}