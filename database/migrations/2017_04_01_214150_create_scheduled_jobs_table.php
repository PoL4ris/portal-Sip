<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_jobs', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('command');
            $table->string('type')->nullable();
            $table->string('enabled');
            $table->string('status')->nullable();
            $table->string('schedule')->nullable();
            $table->text('properties')->nullable();
            $table->timestamp('last_run')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::drop('scheduled_jobs');
    }
}
