<?php

namespace App\Extensions;

use Auth;
use App\Models\ActivityLog;
use Log;

class ActivityLogger {

    public function test(){
        Log::info('Testing ActivityLog class. test() called.');
    }

    public function add($type, $idType, $action, $route, $currData, $newData){

        $newLogEntry = new ActivityLog;
        $newLogEntry->id_users = Auth::user()->id;
        $newLogEntry->type = $type;
        $newLogEntry->id_type = $idType;
        $newLogEntry->action = $action;
        $newLogEntry->route = $route;
        $newLogEntry->log_data = json_encode(array('previous' => $currData,
                                       'new' => $newData));
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