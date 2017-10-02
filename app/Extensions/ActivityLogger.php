<?php

namespace App\Extensions;

use Auth;
use App\Models\ActivityLog;
use Log;

class ActivityLogger {

    public function test(){
        Log::info('Testing ActivityLog class. test() called.');
    }

    /*
     * Function add()
     * $type = Entry type : Customer, Building...
     * $idType = idCustomer
     * $action = Insert/Update
     * $route = Function Triggered
     * $currentData = Old Data
     * $newData = New Data
     * $data = Relation data of the new Data
     * $logType = Front Value to check
     * */
    public function add($type, $idType, $action, $route, $currData, $newData, $data, $logType){

        $newLogEntry              = new ActivityLog;
        $newLogEntry->id_users    = (Auth::user() == null) ? 0 : Auth::user()->id;
        $newLogEntry->type        = $type;
        $newLogEntry->id_type     = $idType;
        $newLogEntry->action      = $action;
        $newLogEntry->route       = $route;
        $newLogEntry->log_data    = json_encode(array('previous'  =>  $currData,
                                                      'new'       =>  $newData,
                                                      'data'      =>  $data,
                                                      'type'      =>  $logType));
        $newLogEntry->save();
    }

    public function get($cid){

        $logEntries = ActivityLog::where('type','customer')
            ->where('id_type', $cid)
            ->get();

        return $logEntries;
    }
}

?>