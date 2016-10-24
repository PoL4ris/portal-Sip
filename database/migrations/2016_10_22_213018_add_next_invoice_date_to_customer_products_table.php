<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNextInvoiceDateToCustomerProductsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->timestamp('next_invoice_date')->after('last_charged')->comment('Next Invoice Date');
        });
    }
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn('next_invoice_date');
        });
    }
}
