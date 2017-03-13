<?php

namespace App\Extensions;

use App\Models\DhcpLease;
use DB;
//use Hash;

class DhcpHandler {

    //    private $testMode = true;
    //    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    public function __construct() {
        DB::connection()->enableQueryLog();
        //        $queries = DB::getQueryLog();
        //        $last_query = end($queries);
        //        dd($last_query);
        //        $configPasscode = config('billing.ippay.passcode');    
        //        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    public function processLeaseRequest($request) {

        if($this->validateRequest($request) == false) {
            dd('$this->validateRequest returned false');
            return false;
        }

        $dhcpRecord = DhcpLease::where('ip',$request['ip'])->first();
        $response = '';

        switch($request['action']) {
                // On Commit:
                //1. Query db 
                //2. If IP exists update the record
                //3. If not then insert new record
                //4. Change status to Active
            case 'COMMIT':
                $request['status'] = 'active';
                if($dhcpRecord === null) {
                    //                    dd('processLeaseRequest(COMMIT): calling $this->insertLease');
                    $response = $this->insertLease($request);
                } else {
                    //                    dd('processLeaseRequest(COMMIT): calling $this->updateLease');
                    $response = $this->updateLease($dhcpRecord, $request);
                }
                break;
                // On Expiry or Release:
                //1. Query db 
                //2. If IP exists update the record
                //3. If not then ignore it
                //4. Change status to Inactive
            case 'EXPIRY':
            case 'RELEASE':
                $request['status'] = 'inactive';
                if($dhcpRecord === null) {
                    //                    dd('processLeaseRequest(COMMIT): calling $this->insertLease');
                    $response = $this->insertLease($request);
                }
                break;
            default:
                break;
        }
        return $response;
    }

    protected function insertLease($request) {
        $newLease = new DhcpLease;
        $newLease->action      = $request['action'];
        $newLease->ip          = $request['ip'];
        $newLease->mac         = $this->formatMacAddress($request['mac']);
        $newLease->date        = $request['date'];
        $newLease->status      = $request['status'];
        $newLease->host_name   = isset($request['host_name']) ? $request['host_name'] : '';
        $newLease->interface   = isset($request['interface']) ? $request['interface'] : '';
        $newLease->vlan        = isset($request['vlan']) ? $request['vlan'] : '';
        $newLease->switch      = isset($request['switch']) ? $this->formatMacAddress($request['switch']) : '';
        $newLease->type        = 'dynamic';
        $newLease->processed   = 'no';
        $newLease->client_id   = $newLease->mac.'-'.$newLease->interface;
        $newLease->save();
        return $newLease->id;
    }

    protected function updateLease($dhcpRecord, $request) {
        $dhcpRecord->action      = $request['action'];
        $dhcpRecord->ip          = $request['ip'];
        $dhcpRecord->mac         = $this->formatMacAddress($request['mac']);
        $dhcpRecord->date        = $request['date'];
        $dhcpRecord->status      = $request['status'];
        $dhcpRecord->host_name   = isset($request['host_name']) ? $request['host_name'] : '';
        $dhcpRecord->interface   = isset($request['interface']) ? $request['interface'] : '';
        $dhcpRecord->vlan        = isset($request['vlan']) ? $request['vlan'] : '';
        $dhcpRecord->switch      = isset($request['switch']) ? $this->formatMacAddress($request['switch']) : '';
        $dhcpRecord->type        = 'dynamic';
        $dhcpRecord->processed   = 'no';
        $dhcpRecord->client_id   = $dhcpRecord->mac.'-'.$dhcpRecord->interface;
        $dhcpRecord->save();
        return $dhcpRecord->id;
    }

    protected function formatMacAddress($mac) {
        $macArray = explode(':', $mac);
        foreach ($macArray as $key => $octet) {
            $macArray[$key] = (strlen($octet) == 1) ? '0'.$octet : $octet;
        }
        return strtoupper(join(':', $macArray));
    }

    protected function validateRequest($request) {

        if(is_array($request) == false) {
            return false;
        }

        if(isset($request['action']) == false ||
           isset($request['ip']) == false ||
           isset($request['date']) == false ||
           isset($request['mac']) == false) {
            return false;
        }

        if($request['action'] != 'COMMIT' &&
           $request['action'] != 'EXPIRY' &&
           $request['action'] != 'RELEASE') {
            return false;
        }

        return true;
    }
}

?>