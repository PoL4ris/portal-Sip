<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIdUsersToNullableInProfilesTable extends Migration
{
  public function up()
  {
    Schema::table('profiles', function (Blueprint $table) {
      $table->integer('id_users')->nullable()->comment("User")->change();
    });
  }
  public function down()
  {
    Schema::table('profiles', function (Blueprint $table) {
      $table->integer('id_users')->change();
    });
  }
}
