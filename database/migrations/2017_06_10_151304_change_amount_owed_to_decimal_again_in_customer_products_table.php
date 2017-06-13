<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountOwedToDecimalAgainInCustomerProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->decimal('amount_owed', 5, 2)->nullable()->change();
        });
    }
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->float('amount_owed')->nullable()->change();
        });
    }
}
