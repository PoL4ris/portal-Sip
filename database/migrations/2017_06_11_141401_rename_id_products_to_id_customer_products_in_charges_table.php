<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameIdProductsToIdCustomerProductsInChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function(Blueprint $table) {
            $table->renameColumn('id_products', 'id_customer_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charges', function(Blueprint $table) {
            $table->renameColumn('id_customer_products', 'id_products');
        });
    }
}
