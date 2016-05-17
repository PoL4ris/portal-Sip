<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('products', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('name');
        $table->string('description');
        $table->integer('id_types')->comment('Type');
        $table->integer('amount');
        $table->string('frequency');
        $table->integer('id_products')->nullable()->comment('Parent');
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
      Schema::drop('products');
    }
}
