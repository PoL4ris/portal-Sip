<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ticket_notes', function(Blueprint $table)
      {
        $table->increments('id');
        $table->integer('id_tickets')->nullable()->comment('Ticket');
        $table->integer('id_ticket_history')->nullable()->comment('Ticket History');
        $table->text('comment');
        $table->integer('id_users')->comment('Created By');
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
      Schema::drop('ticket_notes');
    }
}
