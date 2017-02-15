<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('billing_transaction_logs', function(Blueprint $table)
      {
        $table->increments('id');
        $table->timestamp('date_time')->useCurrent();
        $table->string('transaction_id')->nullable();
        $table->string('username')->nullable();
        $table->integer('id_customers')->nullable();
        $table->string('name')->nullable();
        $table->string('amount')->nullable();
        $table->string('transaction_type')->nullable();
        $table->string('payment_mode')->nullable();
        $table->string('order_number')->nullable();
        $table->string('charge_description')->nullable();
        $table->text('charge_details')->nullable();
        $table->string('action_code')->nullable();
        $table->string('approval')->nullable();
        $table->string('verification')->nullable();
        $table->string('response_text')->nullable();
        $table->string('response_error')->nullable();
        $table->string('address')->nullable();
        $table->string('unit')->nullable();
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
      Schema::drop('billing_transaction_logs');
    }
}
