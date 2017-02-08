<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
  public function up()
  {
    Schema::create('logs', function(Blueprint $table)
    {
      $table->increments('id');
      $table->integer('id_users')->comment('Created By');
      $table->integer('id_customers')->comment('Customer');
      $table->string('action');
      $table->string('route');
      $table->text('data');
      $table->timestamp('timestamp')->comment('Date')->default(DB::raw('CURRENT_TIMESTAMP'));
      $table->timestamps();
    });
  }
  public function down()
  {
    Schema::drop('logs');
  }
}
