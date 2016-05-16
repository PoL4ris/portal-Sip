<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('users', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('first_name')->comment("First Name");
        $table->string('last_name')->comment("Last Name");
        $table->string('email')->unique();
        $table->string('password')->nullable();
        $table->string('remember_token', 100)->nullable();
        $table->string('social_token', 100)->nullable();
        $table->text('avatar', 65535)->nullable();
        $table->string('alias');
        $table->timestamps();
        $table->integer('id_status')->nullable()->comment("Status");
        $table->integer('id_profiles')->nullable()->comment("Profile");
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop('users');
    }
}
