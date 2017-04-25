<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBuildingProprtyValueToLongtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('building_property_values', function (Blueprint $table) {
            $table->longtext('value')->change();
        });
    }
    public function down()
    {
        Schema::table('building_property_values', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }
}
