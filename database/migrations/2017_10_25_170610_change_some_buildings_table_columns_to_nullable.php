<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSomeBuildingsTableColumnsToNullable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('id_neighborhoods')->nullable()->change();
            $table->string('builder')->nullable()->change();
            $table->integer('units')->nullable()->change();
            $table->integer('floors')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('id_neighborhoods')->change();
            $table->string('builder')->change();
            $table->integer('units')->change();
            $table->integer('floors')->change();
        });
    }
}
