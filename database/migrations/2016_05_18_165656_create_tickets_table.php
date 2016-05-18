<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tickets', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_customers')->comment('Customer');
        $table->string('ticket_number')->comment('Ticket Number');
        $table->integer('id_vendors')->nullable()->comment('Vendors');
        $table->integer('id_reasons')->comment('Reason');
        $table->integer('id_ticket_notes')->nullable()->comment('Notes');
        $table->string('status');
        $table->integer('id_users')->comment('Created By');
        $table->integer('id_users_assigned')->comment('Assigned to');
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
      Schema::drop('tickets');
    }
}
