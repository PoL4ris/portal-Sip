<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('building_products', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_buildings')->comment('Building');
        $table->integer('id_products')->comment('Product');
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
      Schema::drop('building_products');
    }
}
