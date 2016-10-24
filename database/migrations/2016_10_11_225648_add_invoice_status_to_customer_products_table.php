<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceStatusToCustomerProductsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->integer('invoice_status')->after('last_charged')->comment('Invoice Status');
        });
    }
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn('invoice_status');
        });
    }
}
