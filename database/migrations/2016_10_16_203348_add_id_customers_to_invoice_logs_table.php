<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdCustomersToInvoiceLogsTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->integer('id_customers')->nullable()->after('id_transactions');
        });
    }
    public function down()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->dropColumn('id_customers');
        });
    }
}
