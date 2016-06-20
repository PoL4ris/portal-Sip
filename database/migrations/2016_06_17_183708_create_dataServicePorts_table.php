<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDataServicePortsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('dataServicePorts', function(Blueprint $table)
    {
      $table->bigInteger('PortID', true);
      $table->text('NodeID');
      $table->text('PortNumber');
      $table->text('IPAddress')->nullable();
      $table->text('MacAddress')->nullable();
      $table->text('Access');
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
    Schema::drop('dataServicePorts');
  }

}
