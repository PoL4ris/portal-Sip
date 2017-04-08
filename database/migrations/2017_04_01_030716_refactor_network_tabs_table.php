<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorNetworkTabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('network_tabs');
        Schema::create('network_tabs', function(Blueprint $table)
                       {
                           $table->increments('id');
                           $table->text('location');
                           $table->text('address');
                           $table->text('core')->nullable();
                           $table->text('dist')->nullable();
                           $table->text('primary')->nullable();
                           $table->text('backup')->nullable();
                           $table->text('mgmt_net')->nullable();
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
        Schema::drop('network_tabs');
        Schema::create('network_tabs', function(Blueprint $table)
                       {
                           $table->integer('NID', true);
                           $table->text('location');
                           $table->text('address');
                           $table->text('core')->nullable();
                           $table->text('dist')->nullable();
                           $table->text('access_up_to_date');
                           $table->text('primary');
                           $table->text('backup')->nullable();
                           $table->text('mgmtnet');
                           $table->integer('segment');
                       });
    }
}
