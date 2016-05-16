<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomerProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('customer_products', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_customers')->comment('-Customer');
        $table->integer('id_products')->comment('-Product');
        $table->integer('id_status')->comment('-Status');
        $table->integer('id_parent')->comment('-Parent');
        $table->date('signed_up')->comment('-Signed up');
        $table->date('expires');
        $table->date('renewed_at')->comment('-Renewed at');
        $table->string('updated_by')->comment('-Updated by');//FK to id_user?
        $table->date('last_charged')->comment('-Last charged');
        $table->integer('amount_owed')->comment('-Amount owed');
        $table->integer('failed_charges_count')->comment('-Failed charges');
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
      Schema::drop('customer_products');
    }
}
