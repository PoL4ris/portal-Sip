<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogsTable extends Migration
{
  public function up()
  {
    Schema::create('activity_logs', function(Blueprint $table)
    {
      $table->increments('id');
      $table->integer('id_users')->comment('Created By');
      $table->string('type');
      $table->integer('id_type');
      $table->string('action');
      $table->string('route');
      $table->text('log_data')->comment('Json Data');
      $table->timestamp('timestamp')->comment('Date')->useCurrent();
      $table->timestamps();
    });
  }
  public function down()
  {
    Schema::drop('activity_logs');
  }
}
