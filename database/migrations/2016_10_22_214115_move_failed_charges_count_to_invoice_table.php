<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveFailedChargesCountToInvoiceTable extends Migration
{
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->dropColumn('failed_charges_count');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('failed_charges_count')->after('status')->comment('Failed Charges Count');
        });


    }
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('failed_charges_count');
        });

        Schema::table('customer_products', function (Blueprint $table) {
            $table->integer('failed_charges_count')->after('amount_owed');
        });
    }
}
