<?php

namespace App\Extensions;

use App\Models\ActivityLog;
use Log;

class ActivityLogger {

    public function test(){
        Log::info('Testing ActivityLog class. test() called.');
    }

    public function add($type, $action, $route, $currData, $newData){

        $newLogEntry = new ActivityLog;
        $newLogEntry->id_users = $id;
        $newLogEntry->type = $type;
        $newLogEntry->action = $action;
        $newLogEntry->route = $route;
        $newLogEntry->log_data = array('previous' => $currData,
                                       'new' => $newData);
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