<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceRecordToInvoiceLogsTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->text('invoice_record')->after('status')->comment('invoice table record');
        });
    }
    public function down()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->dropColumn('invoice_record');
        });
    }
}
