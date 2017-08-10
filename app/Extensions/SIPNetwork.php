<?php

namespace App\Extensions;


use App\Models\Customer;
use App\Models\NetworkNode;
use App\Models\Port;
use App\Extensions\CiscoSwitch;
use App\Extensions\MtikRouter;
use \Carbon\Carbon;
use \Schema;
use DB;
use Log;


class SIPNetwork {

    private $readCommunity;
    private $writeCommunity;
    private $mtikusername;
    private $mtikpassword;
    private $devMode = false;
    private $devModeSwitchIP;
    private $devModeRouterIP;

    public function __construct(){

        $this->readCommunity = config('netmgmt.cisco.read');
        $this->writeCommunity = config('netmgmt.cisco.write');
        $this->mtikusername = config('netmgmt.mikrotik.username');
        $this->mtikpassword = config('netmgmt.mikrotik.password');
        $this->devMode = config('netmgmt.devmode.enabled');
        $this->devModeSwitchIP = config('netmgmt.devmode.switchip');
        $this->devModeRouterIP = config('netmgmt.devmode.routerip');
    }

    protected function getSwitchInstance(){
        return new CiscoSwitch(['readCommunity' => $this->readCommunity,
                                'writeCommunity' => $this->writeCommunity]);
    }

    public function getSwitchPortStatus($portId) {

        $portStatus = array();
        $portStatus['oper-status'] = 'error';
        $portStatus['admin-status'] = 'error';
        $portStatus['port-speed'] = 'error';

        $port = Port::find($portId);
        if($port == null){
            return $portStatus;
        }

        $networkNode = $port->networkNode;
        if($networkNode == null){
            return $portStatus;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switch = $this->getSwitchInstance();

        $portOperStatusResponse = $switch->getSnmpPortOperStatus($switchIP, $switchPort);
        if (!isset($portOperStatusResponse['error'])) {
            $portOperStatus = $portOperStatusResponse['response'];
            switch ($portOperStatus) {
                case '1':
                case 'up(1)':
                    $portOperStatus = 'up';
                    break;
                case '2':
                case 'down(2)':
                    $portOperStatus = 'down';
                    break;
                case '3':
                case 'testing(3)':
                    $portOperStatus = 'testing';
                    break;
                case '4':
                case 'unknown(4)':
                    $portOperStatus = 'unknown';
                    break;
                case '5':
                case 'dormant(5)':
                    $portOperStatus = 'dormant';
                    break;
                case '6':
                case 'notPresent(6)':
                    $portOperStatus = 'not present';
                    break;
                case '7':
                case 'lowerLayerDown(7)':
                    $portOperStatus = 'lower layer down';
                    break;
                default:
                    $portOperStatus = 'not detected';
                    break;
            }
            $portStatus['oper-status'] = $portOperStatus;
        } else {
            $portStatus['oper-status'] = 'error';
        }

        $portAdminStatusResponse = $switch->getSnmpPortAdminStatus($switchIP, $switchPort);
        if (!isset($portAdminStatusResponse['error'])) {
            $portAdminStatus = $portAdminStatusResponse['response'];
            switch ($portAdminStatus) {
                case '1':
                case 'up(1)':
                    $portAdminStatus = 'up';
                    break;
                case '2':
                case 'down(2)':
                    $portAdminStatus = 'down';
                    break;
                case '3':
                case 'testing(3)':
                    $portAdminStatus = 'testing';
                    break;
                default:
                    $portAdminStatus = 'not detected';
                    break;
            }
            $portStatus['admin-status'] = $portAdminStatus;
        } else {
            $portStatus['admin-status'] = 'error';
        }

        $portSpeedResponse = $switch->getSnmpPortSpeed($switchIP, $switchPort);
        if (!isset($portSpeedResponse['error'])) {
            $portSpeedInt = intval($portSpeedResponse['response']) / 1000000;

            if ($portStatus['oper-status'] == 'up') {
                $portStatus['port-speed'] = $portSpeedInt . 'M';
            } else {
                $portStatus['port-speed'] = 'N/A';
            }
        } else {
            $portStatus['port-speed'] = 'error';
        }

        $portStatus['port-status'] = ($portStatus['port-speed'] == 'N/A') ? $portStatus['oper-status'].' (admin: '.$portStatus['admin-status'].')' :
        $portStatus['oper-status'].' (admin: '.$portStatus['admin-status'].', speed: '.$portStatus['port-speed'].')';
        $portStatus['dashboard-port-status'] = ($portStatus['port-speed'] == 'N/A') ? $portStatus['oper-status'] : $portStatus['oper-status'].' at '.$portStatus['port-speed'];

        $lastChangeResponse = $switch->getSnmpPortLastChangeFormatted($switchIP, $switchPort);
        $portStatus['last-change'] = isset($lastChangeResponse['error']) ? 'error' : $lastChangeResponse['response'];

        $sysUptimeResponse = $switch->getSnmpSysUptime($switchIP, $switchPort, true);
        $portStatus['switch-uptime'] = isset($sysUptimeResponse['error']) ? 'error' : $sysUptimeResponse['response'];

        $switchPortModeResponse = $switch->getSnmpSwitchportMode($switchIP, $switchPort);
        $switchPortMode = isset($switchPortModeResponse['error']) ? 'error' : $switchPortModeResponse['response'];

        $portVlanString = '';
        $portVlanResponse = $switch->getSnmpPortVlanAssignment($switchIP, $switchPort);
        $portVlansArr = isset($portVlanResponse['error']) ? [] : $portVlanResponse['response'];

        $numOfVlans = count($portVlansArr);
        if ($numOfVlans > 0 && $numOfVlans < 6) {
            $portVlanString = implode(', ', $portVlansArr);
        } else {
            $portVlanString = 'More than 5';
        }
        $portVlan = $portVlanString . (($switchPortMode == 1) ? ' (Trunk)' : ' (Access)');
        $portStatus['vlan'] = $portVlan;

        return $portStatus;
    }

    public function getAdvSwitchPortStatus($portId) {

        $port = Port::find($portId);
        $errorResponse = false;

        if($port == null){
            return false;
        }

        $networkNode = $port->networkNode;
        if($networkNode == null){
            return false;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;

        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portStatus = array();

            $portfastResponse = $switch->getSnmpPortfastStatus($switchIP, $switchPort);
            $portfastStatus = isset($portfastResponse['error']) ? 'error' : $portfastResponse['response'];

            if ($portfastStatus == '1' || $portfastStatus == 'true(1)') {
                $portStatus['portfast'] = 'Yes';
            } else if($portfastStatus != 'error') {
                $portStatus['portfast'] = 'No';
            } else {
                $portStatus['portfast'] = $portfastStatus;
            }

            $portfastModeResponse = $switch->getSnmpPortfastMode($switchIP, $switchPort);
            $portfastMode = isset($portfastModeResponse['error']) ? 'error' : $portfastModeResponse['response'];

            if ($portfastMode == '1' || $portfastMode == 'enable(1)') {
                $portStatus['portfast-mode'] = 'Enabled';
            } else if ($portfastMode == '2' || $portfastMode == 'disable(2)') {
                $portStatus['portfast-mode'] = 'Disabled';
            } else if ($portfastMode == '3' || $portfastMode == 'trunk(3)') {
                $portStatus['portfast-mode'] = 'Enabled (Trunk)';
            } else if ($portfastMode == '4' || $portfastMode == 'default(4)') {
                $portStatus['portfast-mode'] = 'Default';
            } else {
                $portStatus['portfast-mode'] = $portfastMode;
            }

            $bpduGuardResponse = $switch->getSnmpBpduGuardStatus($switchIP, $switchPort);
            $bpduGuardStatus = isset($bpduGuardResponse['error']) ? 'error' : $bpduGuardResponse['response'];

            if ($bpduGuardStatus == '1' || $bpduGuardStatus == 'enable(1)') {
                $portStatus['bpdu-guard'] = 'Enabled';
            } else if ($bpduGuardStatus == '2' || $bpduGuardStatus == 'disable(2)') {
                $portStatus['bpdu-guard'] = 'Disabled';
            } else if ($bpduGuardStatus == '3' || $bpduGuardStatus == 'default(3)') {
                $portStatus['bpdu-guard'] = 'Default';
            } else {
                $portStatus['bpdu-guard'] = $bpduGuardStatus;
            }

            $bpduFilterResponse = $switch->getSnmpBpduFilterStatus($switchIP, $switchPort);
            $bpduFilterStatus = isset($bpduFilterResponse['error']) ? 'error' : $bpduFilterResponse['response'];

            if ($bpduFilterStatus == '1' || $bpduFilterStatus == 'enable(1)') {
                $portStatus['bpdu-filter'] = 'Enabled';
            } else if ($bpduFilterStatus == '2' || $bpduFilterStatus == 'disable(2)') {
                $portStatus['bpdu-filter'] = 'Disabled';
            } else if ($bpduFilterStatus == '3' || $bpduFilterStatus == 'default(3)') {
                $portStatus['bpdu-filter'] = 'Default';
            } else {
                $portStatus['bpdu-filter'] = $bpduFilterStatus;
            }

            return $portStatus;
        } else {
            return false;
        }
    }

    public function recycleSwitchPort($portId) {

        $port = Port::find($portId);
        if($port == null){
            return false;
        }

        $networkNode = $port->networkNode;
        if($networkNode == null){
            return false;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;

        $portRecyleStatus = false;

        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portRecycleResponse = $switch->snmpPortRecycle($switchIP, $switchPort);
            if(!isset($portRecycleResponse['error'])){
                $portRecyleStatus = $portRecycleResponse['response'];
            }
        }

        if ($portRecyleStatus == true) {
            return true;
        }
        return false;
    }

    public function authenticatePort($portId) {

        $port = Port::find($portId);
        if($port == null){
            return false;
        }

        $networkNode = $port->networkNode;
        if($networkNode == null){
            return false;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;
        $noAccessVlan = 6;

        $portAuthStatus = false;
        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portAuthResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $noAccessVlan);
            if(!isset($portAuthResponse['error'])){
                $portAuthStatus = $portAuthResponse['response'];
                $port->access_level = 'signup';
                $port->save();
            }
        }

        if ($portAuthStatus == true) {
            return true;
        }
        return false;
    }

    public function activatePort($portId) {

        $port = Port::find($portId);
        if($port == null){
            return false;
        }

        $networkNode = $port->networkNode;
        if($networkNode == null){
            return false;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;
        $privateVlan = 6;
        $portActivateStatus = false;

        if ($switchVendor == 'Cisco') {

            $switch = $this->getSwitchInstance();
            $privateVlan = $this->getPrivateVlanByPort($port);
            if ($privateVlan != '') {
                $portActivateResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $privateVlan);
                if(!isset($portActivateResponse['error'])){
                    $portActivateStatus = $portActivateResponse['response'];
                    $port->access_level = 'yes';
                    $port->save();
                }
            } else {
                $accessVlan = 52;
                $portActivateResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $accessVlan);
                if(!isset($portActivateResponse['error'])){
                    $portActivateStatus = $portActivateResponse['response'];
                    $port->access_level = 'yes';
                    $port->save();
                }
            }
        }

        if ($portActivateStatus == true){
            return true;
        }
        return false;
    }

    public function getPrivateVlanByPort(Port $port){

        $netNode = $port->networkNode;
        $vlanRangeStr = $netNode->getProperty('private vlan range');
        if($vlanRangeStr == null){
            return '';
        }
        $switch = $this->getSwitchInstance();
        $portPosition = $switch->getPortPositionByPortNumber($netNode->ip_address, $port->port_number);
        return $this->calculatePrivateVlanFromRange($vlanRangeStr, $portPosition);
    }

    protected function calculatePrivateVlanFromRange($vlanRangeStr, $portPosition) {
        $privateVlan = '';
        if (isset($vlanRangeStr) && $vlanRangeStr != '') {
            $vlanArray = array();
            $vlanRangeChunks = explode(',', $vlanRangeStr);

            foreach($vlanRangeChunks as $range){
                $range = trim($range);
                $vlanRangeArr = explode('-', $range);
                if(empty($vlanArray)) {
                    $vlanArray = range(trim($vlanRangeArr[0]), trim($vlanRangeArr[count($vlanRangeArr) - 1]));
                } else {
                    $vlanArray = array_merge($vlanArray, range(trim($vlanRangeArr[0]), trim($vlanRangeArr[count($vlanRangeArr) - 1])));
                }
            }
            $privateVlan = $vlanArray[$portPosition];
        }
        return $privateVlan;
    }

    public function getPortPrivateVlanByPortID($portID) {

        $port = Port::find($portID);
        $netNode = $port->networkNode;
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->ip_address;
        return $this->getPortPrivateVlanBySwitchIP($switchIP, $port->port_number);
    }

    protected function getPortPrivateVlanBySwitchIP($switchIP, $switchPort) {

        $vlanRangeStr = $this->getNetworkNodePropertyByIPAddress($switchIP, 'private vlan range');
        $switch = $this->getSwitchInstance();
        $portPosition = $switch->getPortPositionByPortNumber($switchIP, $switchPort);

        return $this->calculatePrivateVlanFromRange($vlanRangeStr, $portPosition);
    }

    protected function getNetworkNodePropertyByIPAddress($ipAddress, $propertyName) {

        $netNode = networkNodes::where('IPAddress',$ipAddress)
            ->first();
        $nodePropertyValue = '';
        $nodeProps = $netNode->Properties;
        if ($nodeProps != NULL && $nodeProps != '') {
            $nodePropsArr = json_decode($nodeProps, true);
            if (isset($nodePropsArr[0]) && isset($nodePropsArr[0][$propertyName])) {
                $nodePropertyValue = $nodePropsArr[0][$propertyName];
            }
        }
        return $nodePropertyValue;
    }

    public function getSwitchPortInfoTable($ip, $skipLabelPattern = []) {

        $switchPortInfoArray = array();
        $portTypeRegEx = '/.*ethernet.*/i';
        $ciscoSwitch = $this->getSwitchInstance();

        $portDescArr = $ciscoSwitch->getSnmpAllPortDesc($ip, $portTypeRegEx);
        if(isset($portDescArr['error'])){
            return $switchPortInfoArray;
        }

        $portLabelArr = $ciscoSwitch->getSnmpAllPortLabel($ip, $portTypeRegEx, $skipLabelPattern);
        if(isset($portLabelArr['error'])){
            return $switchPortInfoArray;
        }

        $portOperStatusArr = $ciscoSwitch->getSnmpAllPortOperStatus($ip, $portTypeRegEx);
        if(isset($portOperStatusArr['error'])){
            return $switchPortInfoArray;
        }

        $portAdminStatusArr = $ciscoSwitch->getSnmpAllPortAdminStatus($ip, $portTypeRegEx);
        if(isset($portAdminStatusArr['error'])){
            return $switchPortInfoArray;
        }

        $portSpeedArr = $ciscoSwitch->getSnmpAllPortSpeed($ip, $portTypeRegEx);
        if(isset($portSpeedArr['error'])){
            return $switchPortInfoArray;
        }

        $portLastChangeArr = $ciscoSwitch->getSnmpAllPortLastChangeFormatted($ip, $portTypeRegEx);
        if(isset($portLastChangeArr['error'])){
            return $switchPortInfoArray;
        }

        foreach ($portDescArr['response'] as $key => $desc) {
            $switchPortInfo = array();
            if(isset($portLabelArr['response'][$key]) == false){
                continue;
            }
            $switchPortInfo['Name'] = $desc;
            $switchPortInfo['Label'] = isset($portLabelArr['response'][$key]) ? $portLabelArr['response'][$key] : '';
            $switchPortInfo['Status'] = isset($portOperStatusArr['response'][$key]) ? $portOperStatusArr['response'][$key] : '';
            $switchPortInfo['Speed'] = isset($portSpeedArr['response'][$key]) ?
                (($portSpeedArr['response'][$key] == '1000000000') ? '1G' :
                 (($portSpeedArr['response'][$key] == '100000000') ? '100M' : '10M')) : '';
            $switchPortInfo['LastChange'] = isset($portLastChangeArr['response'][$key]) ? $portLastChangeArr['response'][$key] : '';
            $switchPortInfo['AdminStatus'] = ($portAdminStatusArr['response'][$key] == 'up') ? 'enabled' : 'disabled';
            $switchPortInfoArray[$key] = $switchPortInfo;
        }
        return $switchPortInfoArray;
    }

    public function getSwitchCdpNeighborInfoTable($ip) {

        $switchPortInfoArray = array();
        $portTypeRegEx = '/.*ethernet.*/i';
        $ciscoSwitch = $this->getSwitchInstance();

        $portDescArr = $ciscoSwitch->getSnmpAllPortDesc($ip, $portTypeRegEx);
        if(isset($portDescArr['error'])){
            return $switchPortInfoArray;
        }

        $portLabelArr = $ciscoSwitch->getSnmpAllPortLabel($ip, $portTypeRegEx);
        if(isset($portLabelArr['error'])){
            return $switchPortInfoArray;
        }

        $snmpCdpCacheDeviceIdArr = $ciscoSwitch->getSnmpCdpCacheDeviceId($ip);
        if(isset($snmpCdpCacheDeviceIdArr['error'])){
            return $switchPortInfoArray;
        }

        $snmpCdpCachePlatformArr = $ciscoSwitch->getSnmpCdpCachePlatform($ip);
        if(isset($snmpCdpCachePlatformArr['error'])){
            return $switchPortInfoArray;
        }

        if (count($snmpCdpCacheDeviceIdArr) <= 0) {
            return $switchPortInfoArray;
        }

        foreach ($snmpCdpCacheDeviceIdArr as $key => $devId) {
            $keyArr = explode('.', $key);
            $portIndex = $keyArr[0];
            $switchPortInfo = array();
            $switchPortInfo['Name'] = $portDescArr['response'][$portIndex]; //$desc;
            $switchPortInfo['Label'] = isset($portLabelArr['response'][$portIndex]) ? $portLabelArr['response'][$portIndex] : '';
            $switchPortInfo['NeighborDevId'] = $devId;
            $switchPortInfo['NeighborPlatform'] = $snmpCdpCachePlatformArr[$key];
            $switchPortInfoArray[] = $switchPortInfo;
        }

        return $switchPortInfoArray;
    }

}

?>
