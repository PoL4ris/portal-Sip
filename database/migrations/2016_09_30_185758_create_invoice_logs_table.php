<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('invoice_logs', function(Blueprint $table)
       {
           $table->increments('id');
           $table->string('id_invoices')->nullable();
           $table->string('id_transactions')->nullable();
           $table->string('status')->nullable();
           $table->text('comment')->nullable();
           $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invoice_logs');
    }
}
