<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameInvoiceStatusColumnInCustomerProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_products', function(Blueprint $table) {
            $table->renameColumn('invoice_status', 'charge_status');
            $table->renameColumn('next_invoice_date', 'next_charge_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_products', function(Blueprint $table) {
            $table->renameColumn('charge_status', 'invoice_status');
            $table->renameColumn('next_charge_date', 'next_invoice_date');
        });
    }
}
