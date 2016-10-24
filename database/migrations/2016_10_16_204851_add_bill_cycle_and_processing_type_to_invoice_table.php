<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillCycleAndProcessingTypeToInvoiceTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('processing_type')->nullable()->after('comment');
            $table->integer('bill_cycle_day')->nullable()->after('comment');
        });
    }
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('processing_type');
            $table->dropColumn('bill_cycle_day');
        });
    }
}
