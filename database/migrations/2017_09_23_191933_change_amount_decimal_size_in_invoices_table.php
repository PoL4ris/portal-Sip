<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountDecimalSizeInInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->nullable()->change();
        });
    }
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount', 5, 2)->nullable()->change();
        });
    }
}
