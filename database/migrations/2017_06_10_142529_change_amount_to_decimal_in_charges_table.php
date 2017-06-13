<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountToDecimalInChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->decimal('amount', 5, 2)->nullable()->change();
        });
    }
    public function down()
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->string('amount')->nullable()->change();
        });
    }
}
