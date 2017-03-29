<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecordCountToDataMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_migrations', function (Blueprint $table) {
            $table->integer('total_records')->after('last_updated_at')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_migrations', function (Blueprint $table) {
            $table->dropColumn('total_records');
        });
    }
}
