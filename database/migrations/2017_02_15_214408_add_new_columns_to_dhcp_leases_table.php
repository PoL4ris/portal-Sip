<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToDhcpLeasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dhcp_leases', function (Blueprint $table) {
            $table->string('client_id')->after('switch');
            $table->string('processed')->after('switch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dhcp_leases', function (Blueprint $table) {
            $table->dropColumn('client_id');
            $table->dropColumn('processed');
        });
    }
}
