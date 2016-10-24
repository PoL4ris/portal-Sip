<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountOwedToDecimalInCustomerProductsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->float('amount_owed')->change();
        });
    }
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->integer('amount_owed')->change();
        });
    }
}
