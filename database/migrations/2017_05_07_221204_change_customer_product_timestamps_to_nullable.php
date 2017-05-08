<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomerProductTimestampsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->timestamp('expires')->nullable()->default(null)->change();
            $table->timestamp('renewed_at')->nullable()->default(null)->change();
            $table->timestamp('last_charged')->nullable()->default(null)->change();
            $table->timestamp('next_charge_date')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_products', function (Blueprint $table) {
            $table->timestamp('expires')->nullable(false)->change();
            $table->timestamp('renewed_at')->nullable(false)->change();
            $table->timestamp('last_charged')->nullable(false)->change();
            $table->timestamp('next_charge_date')->nullable(false)->change();
        });
    }
}
