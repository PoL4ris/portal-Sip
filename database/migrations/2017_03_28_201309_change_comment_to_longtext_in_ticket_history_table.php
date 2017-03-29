<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCommentToLongtextInTicketHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_history', function (Blueprint $table) {
            $table->longtext('comment')->change();
        });
    }
    public function down()
    {
        Schema::table('ticket_history', function (Blueprint $table) {
            $table->text('comment')->change();
        });
    }
}
