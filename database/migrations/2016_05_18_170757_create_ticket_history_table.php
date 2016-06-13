<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ticket_history', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_tickets')->comment('Ticket');
        $table->integer('id_reasons')->comment('Reason');
        $table->string('comment')->nullable()->comment('Comment');
        $table->string('status');
        $table->integer('id_users')->comment('Created By');
        $table->integer('id_users_assigned')->nullable()->comment('Assigned to');
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
      Schema::drop('ticket_history');
    }
}
