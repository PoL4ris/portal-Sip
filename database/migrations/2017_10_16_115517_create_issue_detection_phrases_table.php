<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssueDetectionPhrasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issueDetection', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->longText('phrase');
            $table->integer('issue');
            $table->integer('count');
            $table->integer('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('issueDetection');
    }
}
