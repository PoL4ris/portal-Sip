<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPropertyValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('product_property_values', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_products')->comment('Building');
        $table->integer('id_product_properties')->comment('Properties');
        $table->string('value');
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
      Schema::drop('product_property_values');
    }
}
