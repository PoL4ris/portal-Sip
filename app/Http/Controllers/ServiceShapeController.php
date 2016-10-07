<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Schema;
use App\Http\Controllers\NetworkController;


use App\Models\DataShape;


class ServiceShapeController extends Controller
{
  public function __construct(){
    DB::connection()->enableQueryLog();
  }

  public function shapeData(Request $request)
  {
    $input = $request->all();

    $record = DataShape::where('ip',$input['ip'])->first();
    print '<pre>';
    print_r($record);

    $inputData = new DataShape;
    $inputData->action      = $input['action'];
    $inputData->host_name   = $input['host_name'];
    $inputData->ip          = $input['ip'];
    $inputData->mac         = $input['mac'];
    $inputData->interface   = $input['interface'];
    $inputData->vlan        = $input['vlan'];
    $inputData->switch      = $input['switch'];
    $inputData->date        = $input['date'];
    $inputData->type        = 'DHCP';

    if($record)
    {
      if($input['action'] == 'EXPIRY')
        $record->status = $input['action'];
      if($input['action'] == 'RELEASE')
        $record->status = $input['action'];
      if($input['action'] == 'COMMIT')
      {
        $record->action      = $input['action'];
        $record->host_name   = $input['host_name'];
        $record->ip          = $input['ip'];
        $record->mac         = $input['mac'];
        $record->interface   = $input['interface'];
        $record->vlan        = $input['vlan'];
        $record->switch      = $input['switch'];
        $record->date        = $input['date'];
        $record->type        = 'DHCP';
        $record->status      = $input['action'];
      }

        $record->save();
    }
    else
    {
      $inputData->status = $input['action'];
      $inputData->save();
    }

    return;
  }

}
