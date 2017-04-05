<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Charges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->text('details')->nullable();
            $table->string('amount')->nullable();
            $table->integer('id_customers')->nullable();
            $table->integer('id_address')->nullable();
            $table->string('status')->nullable();
            $table->text('comment')->nullable();
            $table->integer('bill_cycle_day')->nullable();
            $table->integer('processing_type')->nullable();
            $table->timestamp('due_date')->nullable();
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
        Schema::drop('charges');
    }
}
