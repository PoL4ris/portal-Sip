<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingPhoneToPaymentMethodsTable extends Migration
{
  public function up()
  {
    Schema::table('payment_methods', function (Blueprint $table) {
      $table->string('billing_phone')->after('types');
    });
  }
  public function down()
  {
    Schema::table('payment_methods', function (Blueprint $table) {
      $table->dropColumn('billing_phone');
    });
  }
}
