<?php

namespace App\Extensions;

use App\Extensions\IpPay;
use App\Models\Customer;
use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\BillingTransactionLog;
use Hash;
use DB;

class SIPNetwork {

    public function __construct() {
        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        //        DB::connection()->enableQueryLog();
    }

    protected function isRequestFromRouter(Request $request){
        $input = $request->all();
        $cid = $input['cid'];
        $amountToCharge = $input['amount'];
        $chargeDesc = $input['desc'];
        
        if($request->has('MacAddress') && $request->has('IPAddress') && $request->has('#LinkLogin') &&
           $request->has('#LinkOrig') && $request->has('ServiceRouter')) {
            return array('MacAddress' => $input['MacAddress'],
            'IPAddress' => $input['IPAddress'],
            'LinkLogin' => $input['#LinkLogin'],
            'LinkOrig' => $input['#LinkOrig'],
            'ServiceRouter' => $input['ServiceRouter'],
            'RequestSource' => 'Router');
        }
        return false;
    }

    public function processRouterData() {
        $userSession = Session::getInstance();
        if ($this->isRequestFromRouter() == false) {
            $userSession->RouterQuery = "Query not from a router";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Query not from a router');
            return false;
        }
        $userSession->techDevice = false;
        //            error_log('$this->config[techTestMacs] = '.print_r($this->config['techTestMacs'],true));
        if (isset($this->config['techTestMacs'][strtolower($_POST['MacAddress'])]) && $this->config['techTestMacs'][strtolower($_POST['MacAddress'])] == 'yes') {
            $userSession->techDevice = true;
        }

        $this->serviceRouter = new MtikRouter(array('HostName' => $_POST['ServiceRouter']));

        if ($this->serviceRouter->isSelected() == false) {
            $userSession->RouterQuery = "router not found";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Router '.$_POST['ServiceRouter'].' not found');
            return false;
        }

        // Find the user's:
        // 1. Real IP lease (universal NAT or private IP)
        // 2. Switch port info from the DHCP lease
        // if($this->config['testMode'] == false){
        $this->userPortInfo = $this->serviceRouter->getUserPortInfo(null, $_POST['IPAddress'], $_POST['MacAddress']);

        if (empty($this->userPortInfo)) {
            $userSession->RouterQuery = "failed";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): User port info not found (empty)');
            return false;
        }

        if (empty($this->userPortInfo['IPAddress'])) {
            $this->userPortInfo['IPAddress'] = $_POST['IPAddress'];
        }
        if (empty($this->userPortInfo['MacAddress'])) {
            $this->userPortInfo['MacAddress'] = $_POST['MacAddress'];
        }

        if (isset($this->userPortInfo['Switch MAC Address']) == false || !$this->userPortInfo['Switch MAC Address']) {
            $userSession->RouterQuery = "switch not found";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Switch not found (no MAC found in Ip lease)');
            return false;
        }

        $userSession->RouterQuery = "succeeded";
        $serviceSwitch = new CiscoSwitch();
        $this->serviceSwitch = $serviceSwitch->loadFromDB(NULL, $this->userPortInfo['Switch MAC Address']);
        if ($this->serviceSwitch == null) {
            $userSession->RouterQuery = "switch not found";
            $userSession->invalidUserIP = true;
            error_log('ERROR: SignupService::processRouterData(): Switch not found in DB');
            return false;
        }

        $userSession->serviceSwitch = $this->serviceSwitch;

        if ($this->serviceSwitch['Model'] == 'WS-C2950-24' || $this->serviceSwitch['Model'] == 'WS-C2950T-24') {
            $this->userPortInfo['Switch Port Number'] += 1;
        } else if (strstr($this->serviceSwitch['Model'], 'WS-C6509')) {
            $this->userPortInfo['Switch Port Number'] = $this->userPortInfo['Switch Instance ID'] . '/' . $this->userPortInfo['Switch Port Number'];
        }
        $serviceLocationInfo = getServiceLocationByLocID($userSession->serviceSwitch['LocID']);
        $userSession->ShortName = $serviceLocationInfo['ShortName'];
        $userSession->PrivateVlan = $this->getPrivateVlan($this->serviceSwitch['NodeID'], $this->userPortInfo['Switch Port Number']);
        $userSession->serviceRouter = $this->serviceRouter->getRouterObject();
        $userSession->serviceRouterName = $_POST['ServiceRouter'];
        $userSession->userPortInfo = $this->userPortInfo;
        return true;
    }

    public function getPrivateVlan($nodeID, $portNumber) {
        $privateVlan = '';
        $privateVlan = getPortPrivateVlanByNodeID($nodeID, $portNumber);
        //        error_log('getPrivateVlan(): $privateVlan = ' . $privateVlan);
        return $privateVlan;
    }

    public function activateUserByPortID($portID = '', $activationCode = '') {
        $userSession = Session::getInstance();
        $userSession->activationResult = 'false';
        $activationResult = false;
        if (!isset($this->userPortInfo) || empty($this->userPortInfo)) {
            $this->userPortInfo = $userSession->userPortInfo;
        }

        if (!isset($this->serviceSwitch) || empty($this->serviceSwitch)) {

            $this->serviceSwitch = new CiscoSwitch($userSession->serviceSwitch);
        }

        if (!isset($this->serviceRouter) || empty($this->serviceRouter)) {
            $this->serviceRouter = new MtikRouter($userSession->serviceRouter);
        }

        if (isset($this->userPortInfo) && !empty($this->userPortInfo)) {
            $userPortNumber = $this->userPortInfo['Switch Port Number'];

            //            if ($this->config['testMode'] == false) {
            $this->serviceRouter->resetUserMacAddress($this->userPortInfo['MacAddress']);
            $this->serviceRouter->enableUserDHCPLease($this->userPortInfo['IPAddress'], $this->userPortInfo['MacAddress']);

            if (isset($userSession->PrivateVlan) && $userSession->PrivateVlan != '') {
                $activationResult = $this->serviceSwitch->setSnmpPortVlanAssignment($this->serviceSwitch->IPAddress, $userPortNumber, $userSession->PrivateVlan);
            } else {
                $activationResult = $this->serviceSwitch->setSnmpPortVlanAssignment($this->serviceSwitch->IPAddress, $userPortNumber, $this->serviceRouter->AccessVLAN);
            }

            $actRez = 'Not Set';
            if ($activationResult == true) {
                $actRez = 'true';
                $userSession->activationResult = 'true';
            }

            $labelResult = $this->serviceSwitch->setSnmpPortLabel($this->serviceSwitch->IPAddress, $userPortNumber, $userSession->ShortName . ' #' . $userSession->Unit);
            //            
            //            
            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            //             $errors['field1'] = 'debug: '.print_r(array('TEST FLAG' => 'false', 'ACT RESULT' => $actRez, 'UserPort' => $userPortNumber, 'Access VLAN' => $this->serviceRouter->AccessVLAN,$this->serviceSwitch->getSwitch()),true);
            //             $fields[1]['displayError'] = true;
            //             throw new FormValidateException($errors);
            //            }
            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            // $errors['field1'] = 'debug: '.print_r(array('TEST FLAG' => 'false', 'ACT RESULT' => $actRez, 'UserPort' => $userPortNumber, 'Access VLAN' => $this->serviceRouter->AccessVLAN,$this->serviceSwitch->getSwitch()),true);
            // $fields[1]['displayError'] = true;
            // throw new FormValidateException($errors);
            //            } else {
            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            // $errors['field1'] = 'debug: '.print_r(array('TEST FLAG' => 'true', 'UserPort' => $userPortNumber, 'Access VLAN' => $this->serviceRouter->AccessVLAN,$this->serviceSwitch->getSwitch()),true);
            // $fields[1]['displayError'] = true;
            // throw new FormValidateException($errors);
            //                $activationResult = snmp2_set('10.10.10.111', 'BigSeem', '1.3.6.1.4.1.9.9.68.1.2.2.1.2.2', 'i', '213', '1000000', '5');
            // $this->serviceSwitch->setSnmpPortVlanAssignment('2', '213', '10.10.10.111');
            //            }
        }
        return $activationResult;
    }

    public function activateUser() {
        $userSession = Session::getInstance();
        $userSession->activationResult = 'false';
        $activationResult = false;
        if (!$this->userPortInfo || empty($this->userPortInfo)) {
            $this->userPortInfo = $userSession->userPortInfo;
        }

        if (!$this->serviceSwitch || empty($this->serviceSwitch)) {
            $this->serviceSwitch = new CiscoSwitch($userSession->serviceSwitch);
        }

        if (!$this->serviceRouter || empty($this->serviceRouter)) {
            $this->serviceRouter = new MtikRouter($userSession->serviceRouter);
        }

        if ($this->userPortInfo && !empty($this->userPortInfo)) {
            $userPortNumber = $this->userPortInfo['Switch Port Number'];


            //            if ($this->config['testMode'] == false) {
            $this->serviceRouter->resetUserMacAddress($this->userPortInfo['MacAddress']);
            $this->serviceRouter->enableUserDHCPLease($this->userPortInfo['IPAddress'], $this->userPortInfo['MacAddress']);
            $activationResult = $this->serviceSwitch->setSnmpPortVlanAssignment($userPortNumber, $this->serviceRouter->AccessVLAN);

            $actRez = 'Not Set';
            if ($activationResult == true) {
                $actRez = 'true';
                $userSession->activationResult = 'true';
            }


            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            // $errors['field1'] = 'debug: '.print_r(array('TEST FLAG' => 'false', 'ACT RESULT' => $actRez, 'UserPort' => $userPortNumber, 'Access VLAN' => $this->serviceRouter->AccessVLAN,$this->serviceSwitch->getSwitch()),true);
            // $fields[1]['displayError'] = true;
            // throw new FormValidateException($errors);
            //            } else {
            // FOR DEBUGGING ONLY. COMMENT THESE OUT IN PROD.
            // $errors['field1'] = 'debug: '.print_r(array('TEST FLAG' => 'true', 'UserPort' => $userPortNumber, 'Access VLAN' => $this->serviceRouter->AccessVLAN,$this->serviceSwitch->getSwitch()),true);
            // $fields[1]['displayError'] = true;
            // throw new FormValidateException($errors);
            //                $activationResult = snmp2_set('10.10.10.111', 'BigSeem', '1.3.6.1.4.1.9.9.68.1.2.2.1.2.2', 'i', '213', '1000000', '5');
            // $this->serviceSwitch->setSnmpPortVlanAssignment('2', '213', '10.10.10.111');
            //            }
        }
        return $activationResult;
    }

    public function getPortID() {
        $portID = NULL;
        if (!$this->userPortInfo || empty($this->userPortInfo)) {
            $this->userPortInfo = $userSession->userPortInfo;
        }

        if (isset($this->userPortInfo['PortID'])) {
            $portID = $this->userPortInfo['PortID'];
        }
        return $portID;
    }

    public function getPortAccess() {
        $userSession = Session::getInstance();
        $portAccess = NULL;

        if (!$this->userPortInfo || empty($this->userPortInfo)) {
            $this->userPortInfo = $userSession->userPortInfo;
        }

        if (!$this->serviceSwitch || empty($this->serviceSwitch)) {
            $this->serviceSwitch = new CiscoSwitch($userSession->serviceSwitch);
        }

        // FOR DEBUGGING ONLY 
        // $userSession->debugMessage = '$this->userPortInfo:<br/>';
        // foreach($this->userPortInfo as $key=>$value){
        // $userSession->debugMessage .= $key.' = '.$value.'<br/>';
        // }
        // $userSession->debugMessage .= 'Node ID: '.$this->serviceSwitch->NodeID.'<br/>';
        // $userSession->debugMessage .= '$this->userPortInfo obj = ';
        // $userSession->debugMessage .= empty($this->userPortInfo) ? 'empty<br/>' : 'full<br/>';

        if ($this->userPortInfo && !empty($this->userPortInfo) && $this->userPortInfo['Switch Port Number']) {
            $portNumber = $this->userPortInfo['Switch Port Number'];
            $nodeID = ($this->serviceSwitch['NodeID']) ? ($this->serviceSwitch['NodeID']) : NULL;

            if ($nodeID) {

                $selectCondArray[] = "`NodeID` = '" . $nodeID . "'";
                $selectCondArray[] = "`PortNumber` = '" . $portNumber . "'";

                $selectConditions = implode(" AND ", $selectCondArray);
                $strSQL = " SELECT * FROM " . DATA_SERVICE_PORTS_TABLE_NAME . ' WHERE ' . $selectConditions;

                // FOR DEBUGGING ONLY
                // $userSession->debugMessage .= 'SQL: '.$strSQL.'<br/>';

                $result = mysql_query($strSQL);
                $fetch = mysql_fetch_array($result);

                if ($fetch && isset($fetch['Access'])) {
                    $portAccess = $fetch['Access'];
                    $this->userPortInfo['PortID'] = $fetch['PortID'];
                    $userSession->userPortInfo = $this->userPortInfo;
                    $userSession->portID = $this->userPortInfo['PortID'];
                }
            }
        }
        return $portAccess;
    }

    public function getUnitFromPort() {
        $userSession = Session::getInstance();
        $unitNumber = NULL;

        if (!$this->userPortInfo || empty($this->userPortInfo)) {
            $this->userPortInfo = $userSession->userPortInfo;
        }


        // FOR DEBUGGING ONLY 
        // $userSession->debugMessage = '$this->userPortInfo:<br/>';
        // foreach($this->userPortInfo as $key=>$value){
        // $userSession->debugMessage .= $key.' = '.$value.'<br/>';
        // }
        // $userSession->debugMessage .= 'Node ID: '.$this->serviceSwitch->NodeID.'<br/>';
        // $userSession->debugMessage .= '$this->userPortInfo obj = ';
        // $userSession->debugMessage .= empty($this->userPortInfo) ? 'empty<br/>' : 'full<br/>';

        if ($this->userPortInfo && !empty($this->userPortInfo) && $this->userPortInfo['PortID']) {

            $portID = $this->userPortInfo['PortID'];

            if ($portID) {

                $selectCondArray[] = "`PortID` = '" . $portID . "'";

                $selectConditions = implode(" AND ", $selectCondArray);
                //                $strSQL = " SELECT * FROM " . USERS_TABLE_NAME . ' WHERE ' . $selectConditions;
                $strSQL = " SELECT * FROM " . TRIAL_USERS_TABLE_NAME . ' WHERE ' . $selectConditions;


                // FOR DEBUGGING ONLY
                // $userSession->debugMessage .= 'SQL: '.$strSQL.'<br/>';

                $result = mysql_query($strSQL);
                $fetch = mysql_fetch_array($result);

                if ($fetch && $fetch['Unit']) {
                    $unitNumber = $fetch['Unit'];
                }
            }
        }
        return $unitNumber;
    }

    public function isPortAlreadyActive() {
        if ($this->getPortAccess() == 'yes') {
            return true;
        }
        return false;
    }

    public function isPortAlreadyRegistered() {
        $userAccessStatus = $this->getPortAccess();
        if ($userAccessStatus != NULL) {
            return true;
        }
        return false;
    }
}

?>
