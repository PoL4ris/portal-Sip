<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTimestampColumnsInDataMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_migrations', function(Blueprint $table) {
            $table->renameColumn('last_created_at', 'max_created_at');
            $table->renameColumn('last_updated_at', 'max_updated_at');
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
            $table->renameColumn('max_created_at', 'last_created_at');
            $table->renameColumn('max_updated_at', 'last_updated_at');
        });
    }
}
