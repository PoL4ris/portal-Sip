<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToBuildingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('building_products', function (Blueprint $table) {
            $table->integer('id_status')->nullable()->after('id_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_products', function (Blueprint $table) {
            $table->dropColumn('id_status');
        });
    }
}
