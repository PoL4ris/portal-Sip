<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataMigrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_migration', function(Blueprint $table) {
            $table->increments('id');
            $table->string('table_name');
            $table->integer('last_processed_id')->nullable();
            $table->timestamp('last_created_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->integer('records_processed')->nullable();
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
      Schema::drop('data_migration');
    }
}
