<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatedChargesTableColumns extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charges', function (Blueprint $table)
        {
            $table->integer('qty')->nullable()->after('amount');
            $table->integer('id_products')->nullable()->after('id_customers');
            $table->string('type')->nullable()->after('status');
            $table->timestamp('end_date')->nullable()->after('processing_type');
            $table->timestamp('start_date')->nullable()->after('processing_type');
        });
    }

    public function down()
    {
        Schema::table('charges', function (Blueprint $table)
        {
            $table->dropColumn('qty');
            $table->dropColumn('id_products');
            $table->dropColumn('type');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
}
