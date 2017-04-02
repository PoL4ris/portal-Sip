<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameLastProcessedIdInDataMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_migrations', function(Blueprint $table) {
            $table->renameColumn('last_processed_id', 'max_processed_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_migrations', function(Blueprint $table) {
            $table->renameColumn('max_processed_id', 'last_processed_id');
        });
    }
}
