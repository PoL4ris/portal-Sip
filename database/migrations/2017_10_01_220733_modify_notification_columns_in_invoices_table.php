<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyNotificationColumnsInInvoicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->renameColumn('notified', 'notified_pastdue');
            $table->renameColumn('last_notified', 'last_notified_pastdue');
            $table->timestamp('last_notified_upcoming')->nullable()->after('last_notified');
            $table->integer('notified_upcoming')->nullable()->after('last_notified');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->renameColumn('notified_pastdue', 'notified');
            $table->renameColumn('last_notified_pastdue', 'last_notified');
            $table->dropColumn('notified_upcoming');
            $table->dropColumn('last_notified_upcoming');
        });
    }
}
