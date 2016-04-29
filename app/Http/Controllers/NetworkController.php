<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Network\DataServicePort;
use App\Models\Network\networkNodes;
use App\Models\Customer\Customers;
use App\Extensions\CiscoSwitch;
use App\Extensions\MtikRouter;
use \Carbon\Carbon;
use \Schema;
use DB;

class NetworkController extends Controller
{
    private $readCommunity;
    private $writeCommunity;
    private $mtikusername;
    private $mtikpassword;
    private $devMode = false;
    private $devModeSwitchIP;
    private $devModeRouterIP;

    public function __construct(){
        $this->theme = 'luna';
        DB::connection()->enableQueryLog();
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

    protected function getRouterInstance(){
        return new MtikRouter(['username' => $this->mtikusername,
                               'password' => $this->mtikpassword]);
    }

    public function getCustomerConnectionInfo($portID) {
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');
       
        return ['Name' => $netNode->HostName,
                     'IP' => $netNode->IPAddress,
                     'Port' => $servicePort->PortNumber,
                     'Access' => $servicePort->Access,
                     'Vendor' => $netNode->Vendor,
                     'Model' => $netNode->Model
                    ];
    }
    
    public function getSwitchPortStatus(Request $request) {

        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switch = $this->getSwitchInstance();

        $portOperStatus = $switch->getSnmpPortOperStatus($switchIP, $switchPort);
        if ($portOperStatus) {
            $portOperStatus = $this->formatSnmpResponse($portOperStatus);
            switch ($portOperStatus) {
                case 'up(1)':
                    $portOperStatus = 'up';
                    break;
                case 'down(2)':
                    $portOperStatus = 'down';
                    break;
                case 'testing(3)':
                    $portOperStatus = 'testing';
                    break;
                case 'unknown(4)':
                    $portOperStatus = 'unknown';
                    break;
                case 'dormant(5)':
                    $portOperStatus = 'dormant';
                    break;
                case 'notPresent(6)':
                    $portOperStatus = 'not present';
                    break;
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

        $portAdminStatus = $switch->getSnmpPortAdminStatus($switchIP, $switchPort);
        if ($portAdminStatus) {
            $portAdminStatus = $this->formatSnmpResponse($portAdminStatus);
            switch ($portAdminStatus) {
                case 'up(1)':
                    $portAdminStatus = 'up';
                    break;
                case 'down(2)':
                    $portAdminStatus = 'down';
                    break;
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

        $portSpeed = $switch->getSnmpPortSpeed($switchIP, $switchPort);
        if ($portSpeed) {
            $portSpeedInt = intval($this->formatSnmpResponse($portSpeed)) / 1000000;
            if ($portStatus['oper-status'] == 'up') {
                $portStatus['port-speed'] = $portSpeedInt . 'M';
            } else {
                $portStatus['port-speed'] = 'N/A';
            }
        } else {
            $portStatus['port-speed'] = 'error';
        }

        $portStatus['last-change'] = $switch->getSnmpPortLastChangeFormatted($switchIP, $switchPort);
        $portStatus['switch-uptime'] = $switch->getSnmpSysUptimeFormatted($switchIP, $switchPort);

        $switchPortMode = $switch->getSnmpSwitchportMode($switchIP, $switchPort);
        $portVlanString = '';
        $portVlansArr = $switch->getSnmpPortVlanAssignment($switchIP, $switchPort);

        if (count($portVlansArr) > 0) {
            $portVlanString = implode(', ', $portVlansArr);
        }
        $portVlan = $portVlanString . (($switchPortMode == 1) ? ' (Trunk)' : ' (Access)');
        $portStatus['vlan'] = $portVlan;

        return $portStatus;
    }

    public function getAdvSwitchPortStatus(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switchMAC = $netNode->MacAddress;
        $switchVendor = $netNode->Vendor;
        $switchModel = $netNode->Model;

        $errorResponse = false;

        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portStatus = array();

            $portfastStatus = $switch->getSnmpPortfastStatus($switchIP, $switchPort);
            if ($portfastStatus == '1' || $portfastStatus == 'true(1)') {
                $portStatus['portfast'] = 'Yes';
            } else {
                $portStatus['portfast'] = 'No';
            }

            $portfastMode = $switch->getSnmpPortfastMode($switchIP, $switchPort);
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

            $bpduGuardStatus = $switch->getSnmpBpduGuardStatus($switchIP, $switchPort);
            if ($bpduGuardStatus == '1' || $bpduGuardStatus == 'enable(1)') {
                $portStatus['bpdu-guard'] = 'Enabled';
            } else if ($bpduGuardStatus == '2' || $bpduGuardStatus == 'disable(2)') {
                $portStatus['bpdu-guard'] = 'Disabled';
            } else if ($bpduGuardStatus == '3' || $bpduGuardStatus == 'default(3)') {
                $portStatus['bpdu-guard'] = 'Default';
            } else {
                $portStatus['bpdu-guard'] = $bpduGuardStatus;
            }

            $bpduFilterStatus = $switch->getSnmpBpduFilterStatus($switchIP, $switchPort);
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

    public function recycleSwitchPort(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switchVendor = $netNode->Vendor;

        $portOperStatus = false;

        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portOperStatus = $switch->snmpPortRecycle($switchIP, $switchPort);
        }

        if ($portOperStatus == true)
          return $this->getSwitchPortStatus($request);
        else
          return 'ERROR';

    }

    public function getPortActiveIPs(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        return $this->getActiveLeasesOnPort($portID);
    }

    public function getPortAllIPs(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];

        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');        
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $routerNode = $this->getRouterByPortID($portID);
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        return $this->getAllLeasesOnPort($routerIP, $switchIP, $switchPort);
    }

    public function authenticatePort(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();
        $servicePort->Access = 'signup';
        $servicePort->LastUpdated = Carbon::now()->toDateTimeString();
        $servicePort->save();

        $netNode = $servicePort->getRelationValue('networkNode');        
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switchVendor = $netNode->Vendor;
        $routerNode = $this->getRouterByPortID($portID);
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        $noAccessVlan = $routerNode->NoAccessVLAN;

        $portOperStatus = false;

        if (!isset($noAccessVlan) || $noAccessVlan == '') {
            $ipInfoArr = $this->getActiveLeasesOnPort($routerIP);
            if ($ipInfoArr != false) {
                $router = $this->getRouterInstance();
                foreach ($ipInfoArr as $leaseInfo) {
                    $router->disableUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
                }
            }
        }
        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $portOperStatus = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $noAccessVlan);
        }

        if ($portOperStatus == true)
          return $this->getSwitchPortStatus($request);
        else
          return 'ERROR';

    }

    public function activatePort(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();
        $servicePort->Access = 'yes';
        $servicePort->LastUpdated = Carbon::now()->toDateTimeString();
        $servicePort->save();

        $netNode = $servicePort->getRelationValue('networkNode');        
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switchVendor = $netNode->Vendor;
        $routerNode = $this->getRouterByPortID($portID);
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        $privateVlan = $routerNode->NoAccessVLAN;
        $portOperStatus = false;

        if ($switchVendor == 'Cisco') {
            $switch = $this->getSwitchInstance();
            $privateVlan = $this->getPortPrivateVlanBySwitchIP($switchIP, $switchPort);
            if (isset($privateVlan) && $privateVlan != '') {
                $portOperStatus = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $privateVlan);
            } else {
                $portOperStatus = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $routerNode->AccessVLAN);
            }
        }

        $ipInfoArr = $this->getAllLeasesOnPort($routerIP, $switchIP, $switchPort);
        if ($ipInfoArr != false) {
            $router = $this->getRouterInstance();
            foreach ($ipInfoArr as $leaseInfo) {
                if (isset($leaseInfo['comment'])) {
                    $router->enableUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
                } else {
                    $router->removeUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
                }
            }
        }

        if ($portOperStatus == true)
          return $this->getSwitchPortStatus($request);
        else
          return 'ERROR';
    }

    public function removeLease(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $leaseID = $input['leaseID'];

        $routerNode = $this->getRouterByPortID($portID);
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        $router = $this->getRouterInstance();
        $routerActionResult = $router->removeUserDHCPLeaseByID($leaseID, $routerIP);
        return array('Status' => 'Removed');
    }

    public function reserveLease(Request $request) {
        $input = $request->all();
        $leaseID = $input['leaseID'];
        $portID = $input['portid'];
        $CID = $input['CID'];

        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');        
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $routerNode = $this->getRouterByPortID($portID);
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;

        $customer = Customers::where('CID',$CID)
            ->first();
        $LocCode = $customer->LocCode;
        $UnitNumber = $customer->UnitNumber;
        $portOperStatus = false;

        $router = $this->getRouterInstance();
        $routerActionResult = $router->reserveUserDHCPLeaseByID($leaseID, $LocCode, $UnitNumber, $routerIP);

        return array('Status' => 'Reserved');
    }

    public function getRouterInfoByPortID(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        return $this->getRouterByPortID($portID);
    }

    public function getPortPrivateVlanByPortID(Request $request) {
        $input = $request->all();
        $portID = $input['portid'];
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');        
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;

        return $this->getPortPrivateVlanBySwitchIP($switchIP, $switchPort);
    }

    protected function getRouterByPortID($portID) {
        $servicePort = DataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();
        $netNode = $servicePort->getRelationValue('networkNode');

        return networkNodes::where('LocID', $netNode->LocID)
            ->where('Type','Router')
            ->where('Role','Master')
            ->first();
    }

    protected function getActiveLeasesOnPort($portID, $comment = '') {
        $userIPInfoArr = array();
        $servicePort = dataServicePort::with('networkNode')
            ->where('PortID',$portID)
            ->first();

        $netNode = $servicePort->getRelationValue('networkNode');
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        $switchPort = $servicePort->PortNumber;
        $switch = $this->getSwitchInstance();
        $routerNode = $this->getRouterByPortID($portID);
        $router = $this->getRouterInstance();
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        $userIPInfoArr = $router->getDHCPLeasesBySwitchPort($routerIP, $switchIP, $switchPort);

        if ($comment != '') {
            $ipByCommentArray = $router->getDHCPLeasesByComment($routerIP, $comment);
            if (count($ipByCommentArray) > 0) {
                $userIPInfoArr = array_merge($userIPInfoArr, $ipByCommentArray);
            }
        }
        return $userIPInfoArr;
    }

    protected function getAllLeasesOnPort($routerIP, $switchIP, $switchPort) {
        $userIPInfoArr = array();
        $router = $this->getRouterInstance();
        $userIPInfoArr = $router->getAllDHCPLeasesBySwitchPort($routerIP, $switchIP, $switchPort);
        return $userIPInfoArr;
    }

    protected function getPortPrivateVlanBySwitchIP($switchIP, $switchPort) {
        $vlanRangeStr = $this->getNetworkNodePropertyByIPAddress($switchIP, 'private vlan range');    
        $switch = $this->getSwitchInstance();
        $portPosition = $switch->getPortPositionByPortNumber($switchIP, $switchPort);

        return $this->getSwitchPrivateVlanByRange($vlanRangeStr, $portPosition);
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

    protected function getSwitchPrivateVlanByRange($vlanRangeStr, $portPosition) {
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

    public function formatSnmpResponse($snmpResponse) {
        if ($snmpResponse != '') {
            $snmpRespStr = preg_replace('/.+:/', '', $snmpResponse);
            return trim($snmpRespStr);
        }
        return false;
    }

}
    