<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceAndUserIdColumnsToChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table)
        {
            $table->bigInteger('id_invoices')->nullable()->after('id_address');
            $table->bigInteger('id_users')->nullable()->after('id_invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropColumn('id_invoices');
            $table->dropColumn('id_users');
        });
    }
}
