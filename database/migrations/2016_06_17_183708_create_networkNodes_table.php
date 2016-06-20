<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNetworkNodesTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('networkNodes', function(Blueprint $table)
    {
      $table->bigInteger('NodeID', true);
      $table->text('HostName');
      $table->text('IPAddress');
      $table->text('MacAddress')->nullable();
      $table->text('Type')->nullable();
      $table->text('Role');
      $table->text('LocID')->nullable();
      $table->text('AccessVLAN')->nullable();
      $table->text('NoAccessVLAN')->nullable();
      $table->text('Vendor');
      $table->text('Model');
      $table->text('Properties', 65535)->nullable();
      $table->text('Comments', 65535)->nullable();
      $table->timestamp('LastUpdated')->default(DB::raw('CURRENT_TIMESTAMP'));
    });
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('networkNodes');
  }

}
