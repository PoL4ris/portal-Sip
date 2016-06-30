<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
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
        $table->string('password')->nullable()->comment('disable');
        $table->string('remember_token', 100)->nullable()->comment('disable');
        $table->string('social_token', 100)->nullable()->comment('disable');
        $table->integer('social_access')->comment('sino');
        $table->text('avatar', 65535)->nullable();
        $table->string('alias')->comment('disable');
        $table->integer('id_status')->nullable()->comment("Status");
        $table->integer('id_profiles')->nullable()->comment("Profile");
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
      Schema::drop('users');
    }
}
