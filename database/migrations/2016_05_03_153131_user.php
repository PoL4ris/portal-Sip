<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('user', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('remember_token', 100)->nullable();
        $table->string('social_token', 100)->nullable();
        $table->text('avatar', 65535)->nullable();;
        $table->string('alias');
        $table->timestamps();
        $table->integer('status');
        $table->integer('id_profile');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop('user');
    }
}
