<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressToCustomerProductsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->integer('id_address')->after('id_status')->comment('Service Address');
        });
    }
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn('id_address');
        });
    }
}
