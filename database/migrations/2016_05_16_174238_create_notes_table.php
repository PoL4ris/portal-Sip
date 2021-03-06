<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('notes', function(Blueprint $table)
      {
        $table->increments('id');
        $table->text('comment')->comment('Comment');
        $table->string('created_by')->comment('Created by');//FK to id_users?
        $table->integer('id_customers')->nullable()->comment('Customer');
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
      Schema::drop('notes');
    }
}
