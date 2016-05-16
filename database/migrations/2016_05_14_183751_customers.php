<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('customers', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('first_name')->comment("First Name");
        $table->string('last_name')->comment("Last Name");
        $table->string('email')->unique();
        $table->string('password')->nullable();
        $table->string('company')->nullable();
        $table->string('vip')->nullable();
        $table->integer('id_types')->nullable()->comment("Type-Type");
        $table->integer('id_status')->nullable()->comment("Status-Status");
        $table->date('signedup_at')->comment('Signed up at');
        $table->date('canceled_at')->comment('Canceled at');
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
      Schema::drop('customers');
    }
}
