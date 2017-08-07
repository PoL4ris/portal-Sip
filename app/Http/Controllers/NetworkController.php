<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

// LEGACY MODELS - REMOVE
use App\Models\DataServicePort;
use App\Models\Network\networkNodes;

// NEW MODELS
use App\Models\Customer;
use App\Models\NetworkNode;
use App\Models\Port;

use App\Extensions\SIPNetwork;
use App\Extensions\CiscoSwitch;
use App\Extensions\MtikRouter;
use \Carbon\Carbon;
use \Schema;
use DB;
use Log;

class NetworkController extends Controller {

    private $readCommunity;
    private $writeCommunity;
    private $mtikusername;
    private $mtikpassword;
    private $devMode = false;
    private $devModeSwitchIP;
    private $devModeRouterIP;

    public function __construct()
    {
        //        $this->theme = 'luna';
        //        DB::connection()->enableQueryLog();
        $this->readCommunity = config('netmgmt.cisco.read');
        $this->writeCommunity = config('netmgmt.cisco.write');
        $this->mtikusername = config('netmgmt.mikrotik.username');
        $this->mtikpassword = config('netmgmt.mikrotik.password');
        $this->devMode = config('netmgmt.devmode.enabled');
        $this->devModeSwitchIP = config('netmgmt.devmode.switchip');
        $this->devModeRouterIP = config('netmgmt.devmode.routerip');
    }

    protected function getSwitchInstance()
    {
        return new CiscoSwitch(['readCommunity'  => $this->readCommunity,
                                'writeCommunity' => $this->writeCommunity]);
    }

    public function getSwitchPortStatus(Request $request)
    {

        $input = $request->all();
        $portId = $input['portid'];
        $portStatus = array();
        $portStatus['oper-status'] = 'error';
        $portStatus['admin-status'] = 'error';
        $portStatus['port-speed'] = 'error';

        $port = Port::find($portId);
        if ($port == null)
        {
            return $portStatus;
        }

        $networkNode = $port->networkNode;
        if ($networkNode == null)
        {
            return $portStatus;
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switch = $this->getSwitchInstance();

        $portOperStatusResponse = $switch->getSnmpPortOperStatus($switchIP, $switchPort);
        if ( ! isset($portOperStatusResponse['error']))
        {
            $portOperStatus = $portOperStatusResponse['response'];
            switch ($portOperStatus)
            {
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
        } else
        {
            $portStatus['oper-status'] = 'error';
        }

        $portAdminStatusResponse = $switch->getSnmpPortAdminStatus($switchIP, $switchPort);
        if ( ! isset($portAdminStatusResponse['error']))
        {
            $portAdminStatus = $portAdminStatusResponse['response'];
            switch ($portAdminStatus)
            {
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
        } else
        {
            $portStatus['admin-status'] = 'error';
        }

        $portSpeedResponse = $switch->getSnmpPortSpeed($switchIP, $switchPort);
        if ( ! isset($portSpeedResponse['error']))
        {
            $portSpeedInt = intval($portSpeedResponse['response']) / 1000000;

            if ($portStatus['oper-status'] == 'up')
            {
                $portStatus['port-speed'] = $portSpeedInt . 'M';
            } else
            {
                $portStatus['port-speed'] = 'N/A';
            }
        } else
        {
            $portStatus['port-speed'] = 'error';
        }

        $portStatus['port-status'] = ($portStatus['port-speed'] == 'N/A') ? $portStatus['oper-status'] . ' (admin: ' . $portStatus['admin-status'] . ')' :
            $portStatus['oper-status'] . ' (admin: ' . $portStatus['admin-status'] . ', speed: ' . $portStatus['port-speed'] . ')';
        $portStatus['dashboard-port-status'] = ($portStatus['port-speed'] == 'N/A') ? $portStatus['oper-status'] : $portStatus['oper-status'] . ' at ' . $portStatus['port-speed'];

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
        if ($numOfVlans > 0 && $numOfVlans < 6)
        {
            $portVlanString = implode(', ', $portVlansArr);
        } else
        {
            $portVlanString = 'More than 5';
        }
        $portVlan = $portVlanString . (($switchPortMode == 1) ? ' (Trunk)' : ' (Access)');
        $portStatus['vlan'] = $portVlan;

        return $portStatus;
    }

    public function getAdvSwitchPortStatus(Request $request)
    {

        $input = $request->all();
        $portId = $input['portid'];
        $port = Port::find($portId);

        $errorResponse = false;

        if ($port == null)
        {
            return 'ERROR';
        }

        $networkNode = $port->networkNode;
        if ($networkNode == null)
        {
            return 'ERROR';
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;

        if ($switchVendor == 'Cisco')
        {
            $switch = $this->getSwitchInstance();
            $portStatus = array();

            $portfastResponse = $switch->getSnmpPortfastStatus($switchIP, $switchPort);
            $portfastStatus = isset($portfastResponse['error']) ? 'error' : $portfastResponse['response'];

            if ($portfastStatus == '1' || $portfastStatus == 'true(1)')
            {
                $portStatus['portfast'] = 'Yes';
            } else if ($portfastStatus != 'error')
            {
                $portStatus['portfast'] = 'No';
            } else
            {
                $portStatus['portfast'] = $portfastStatus;
            }

            $portfastModeResponse = $switch->getSnmpPortfastMode($switchIP, $switchPort);
            $portfastMode = isset($portfastModeResponse['error']) ? 'error' : $portfastModeResponse['response'];

            if ($portfastMode == '1' || $portfastMode == 'enable(1)')
            {
                $portStatus['portfast-mode'] = 'Enabled';
            } else if ($portfastMode == '2' || $portfastMode == 'disable(2)')
            {
                $portStatus['portfast-mode'] = 'Disabled';
            } else if ($portfastMode == '3' || $portfastMode == 'trunk(3)')
            {
                $portStatus['portfast-mode'] = 'Enabled (Trunk)';
            } else if ($portfastMode == '4' || $portfastMode == 'default(4)')
            {
                $portStatus['portfast-mode'] = 'Default';
            } else
            {
                $portStatus['portfast-mode'] = $portfastMode;
            }

            $bpduGuardResponse = $switch->getSnmpBpduGuardStatus($switchIP, $switchPort);
            $bpduGuardStatus = isset($bpduGuardResponse['error']) ? 'error' : $bpduGuardResponse['response'];

            if ($bpduGuardStatus == '1' || $bpduGuardStatus == 'enable(1)')
            {
                $portStatus['bpdu-guard'] = 'Enabled';
            } else if ($bpduGuardStatus == '2' || $bpduGuardStatus == 'disable(2)')
            {
                $portStatus['bpdu-guard'] = 'Disabled';
            } else if ($bpduGuardStatus == '3' || $bpduGuardStatus == 'default(3)')
            {
                $portStatus['bpdu-guard'] = 'Default';
            } else
            {
                $portStatus['bpdu-guard'] = $bpduGuardStatus;
            }

            $bpduFilterResponse = $switch->getSnmpBpduFilterStatus($switchIP, $switchPort);
            $bpduFilterStatus = isset($bpduFilterResponse['error']) ? 'error' : $bpduFilterResponse['response'];

            if ($bpduFilterStatus == '1' || $bpduFilterStatus == 'enable(1)')
            {
                $portStatus['bpdu-filter'] = 'Enabled';
            } else if ($bpduFilterStatus == '2' || $bpduFilterStatus == 'disable(2)')
            {
                $portStatus['bpdu-filter'] = 'Disabled';
            } else if ($bpduFilterStatus == '3' || $bpduFilterStatus == 'default(3)')
            {
                $portStatus['bpdu-filter'] = 'Default';
            } else
            {
                $portStatus['bpdu-filter'] = $bpduFilterStatus;
            }

            return $portStatus;
        } else
        {
            return 'ERROR';
        }
    }

    public function recycleSwitchPort(Request $request)
    {

        $input = $request->all();
        $portId = $input['portid'];

        $port = Port::find($portId);
        if ($port == null)
        {
            return 'ERROR';
        }

        $networkNode = $port->networkNode;
        if ($networkNode == null)
        {
            return 'ERROR';
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;

        $portRecyleStatus = false;

        if ($switchVendor == 'Cisco')
        {
            $switch = $this->getSwitchInstance();
            $portRecycleResponse = $switch->snmpPortRecycle($switchIP, $switchPort);
            if ( ! isset($portRecycleResponse['error']))
            {
                $portRecyleStatus = $portRecycleResponse['response'];
            }
        }

        if ($portRecyleStatus == true)
        {
            return $this->getSwitchPortStatus($request);
        }

        return 'ERROR';
    }

    public function authenticatePort(Request $request)
    {

        $input = $request->all();
        $portId = $input['portid'];

        $port = Port::find($portId);
        if ($port == null)
        {
            return 'ERROR';
        }

        $networkNode = $port->networkNode;
        if ($networkNode == null)
        {
            return 'ERROR';
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;
        $noAccessVlan = 6;

        //        $routerNode = $this->getRouterByPortID($request->portid);
        //        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;//ip_address
        //        $noAccessVlan = $routerNode->NoAccessVLAN;


        //        if (!isset($noAccessVlan) || $noAccessVlan == '') {
        //            $ipInfoArr = $this->getActiveLeasesOnPort($routerIP);
        //            if ($ipInfoArr != false) {
        //                $router = $this->getRouterInstance();
        //                foreach ($ipInfoArr as $leaseInfo) {
        //                    $router->disableUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
        //                }
        //            }
        //        }

        $portAuthStatus = false;
        if ($switchVendor == 'Cisco')
        {
            $switch = $this->getSwitchInstance();
            $portAuthResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $noAccessVlan);
            if ( ! isset($portAuthResponse['error']))
            {
                $portAuthStatus = $portAuthResponse['response'];
                $port->access_level = 'signup';
                $port->save();
            }
        }

        if ($portAuthStatus == true)
        {
            return $this->getSwitchPortStatus($request);
        }

        return 'ERROR';
    }

    public function activatePort(Request $request)
    {

        $input = $request->all();
        $portId = $input['portid'];

        $port = Port::find($portId);
        if ($port == null)
        {
            return 'ERROR';
        }

        $networkNode = $port->networkNode;
        if ($networkNode == null)
        {
            return 'ERROR';
        }

        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $networkNode->ip_address;
        $switchPort = $port->port_number;
        $switchVendor = $networkNode->vendor;
        $privateVlan = 6;
        $portActivateStatus = false;

        //        $input = $request->all();
        //        $portID = $input['portid'];
        //        $servicePort = dataServicePort::with('networkNode')
        //            ->where('PortID',$portID)
        //            ->first();
        //        $servicePort->Access = 'yes';
        //        $servicePort->LastUpdated = Carbon::now()->toDateTimeString();
        //        $servicePort->save();

        //        $netNode = $servicePort->getRelationValue('networkNode');
        //        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
        //        $switchPort = $servicePort->PortNumber;
        //        $switchVendor = $netNode->Vendor;
        //        $routerNode = $this->getRouterByPortID($portID);
        //        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        //        $privateVlan = $routerNode->NoAccessVLAN;
        //        $portOperStatus = false;

        if ($switchVendor == 'Cisco')
        {

            $switch = $this->getSwitchInstance();
            //            $privateVlan = $this->getPortPrivateVlanBySwitchIP($switchIP, $switchPort);
            $privateVlan = $this->getPrivateVlanByPort($port);
            if ($privateVlan != '')
            {
                //            if (isset($privateVlan) && $privateVlan != '') {
                $portActivateResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $privateVlan);
                if ( ! isset($portActivateResponse['error']))
                {
                    $portActivateStatus = $portActivateResponse['response'];
                    $port->access_level = 'yes';
                    $port->save();
                }
            } else
            {
                $accessVlan = 52;
                $portActivateResponse = $switch->setSnmpPortVlanAssignment($switchIP, $switchPort, $accessVlan);
                if ( ! isset($portActivateResponse['error']))
                {
                    $portActivateStatus = $portActivateResponse['response'];
                    $port->access_level = 'yes';
                    $port->save();
                }
            }
        }

        //        $ipInfoArr = $this->getAllLeasesOnPort($routerIP, $switchIP, $switchPort);
        //        if ($ipInfoArr != false) {
        //            $router = $this->getRouterInstance();
        //            foreach ($ipInfoArr as $leaseInfo) {
        //                if (isset($leaseInfo['comment'])) {
        //                    $router->enableUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
        //                } else {
        //                    $router->removeUserDHCPLeaseByID($leaseInfo['.id'], $routerIP);
        //                }
        //            }
        //        }

        if ($portActivateStatus == true)
        {
            return $this->getSwitchPortStatus($request);
        }

        return 'ERROR';
    }

    public function getPrivateVlanByPort(Port $port)
    {

        $netNode = $port->networkNode;
        $vlanRangeStr = $netNode->getProperty('private vlan range');
        if ($vlanRangeStr == null)
        {
            return '';
        }
        $switch = $this->getSwitchInstance();
        $portPosition = $switch->getPortPositionByPortNumber($netNode->ip_address, $port->port_number);

        return $this->calculatePrivateVlanFromRange($vlanRangeStr, $portPosition);
    }

    protected function calculatePrivateVlanFromRange($vlanRangeStr, $portPosition)
    {
        $privateVlan = '';
        if (isset($vlanRangeStr) && $vlanRangeStr != '')
        {
            $vlanArray = array();
            $vlanRangeChunks = explode(',', $vlanRangeStr);

            foreach ($vlanRangeChunks as $range)
            {
                $range = trim($range);
                $vlanRangeArr = explode('-', $range);
                if (empty($vlanArray))
                {
                    $vlanArray = range(trim($vlanRangeArr[0]), trim($vlanRangeArr[count($vlanRangeArr) - 1]));
                } else
                {
                    $vlanArray = array_merge($vlanArray, range(trim($vlanRangeArr[0]), trim($vlanRangeArr[count($vlanRangeArr) - 1])));
                }
            }
            $privateVlan = $vlanArray[$portPosition];
        }

        return $privateVlan;
    }

    public function getPortPrivateVlanByPortID(Request $request)
    {

        $input = $request->all();
        $portID = $input['portid'];
        $port = Port::find($portID);
        $netNode = $port->networkNode;
        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->ip_address;

        return $this->getPortPrivateVlanBySwitchIP($switchIP, $port->port_number);
    }

    protected function getPortPrivateVlanBySwitchIP($switchIP, $switchPort)
    {

        //        $netNode
        $vlanRangeStr = $this->getNetworkNodePropertyByIPAddress($switchIP, 'private vlan range');
        $switch = $this->getSwitchInstance();
        $portPosition = $switch->getPortPositionByPortNumber($switchIP, $switchPort);

        return $this->calculatePrivateVlanFromRange($vlanRangeStr, $portPosition);
    }

    protected function getNetworkNodePropertyByIPAddress($ipAddress, $propertyName)
    {

        $netNode = networkNodes::where('IPAddress', $ipAddress)
            ->first();
        $nodePropertyValue = '';
        $nodeProps = $netNode->Properties;
        if ($nodeProps != null && $nodeProps != '')
        {
            $nodePropsArr = json_decode($nodeProps, true);
            if (isset($nodePropsArr[0]) && isset($nodePropsArr[0][$propertyName]))
            {
                $nodePropertyValue = $nodePropsArr[0][$propertyName];
            }
        }

        return $nodePropertyValue;
    }

    public function portTdrTest(Request $request)
    {
        $running = 0;

        $ciscoSwitch = new CiscoSwitch(['readCommunity'  => 'oomoomee',
                                        'writeCommunity' => 'BigSeem']);

//        $portTypeRegEx = '/.*ethernet.*/i';
//        $skipLabelPattern = ['/.*[uU]plink.*/i', '/.*[dD]ownlink.*/i'];
//        $portLabels = $ciscoSwitch->getSnmpAllPortLabel('10.11.123.27', $portTypeRegEx, $skipLabelPattern);


        $currentAction = $ciscoSwitch->getSnmpTdrIfAction($request->ip, $request->port);

        switch ($currentAction['response'])
        {
            case '3':
                //please wait test is already running
                $running = 1;

                return "please wait for current test to finish";
                break;
            case '4':
                //test is not running, lets run test
                // echo "starting snmp test<br>";
                $ciscoSwitch->setSnmpTdrIfAction($request->ip, $request->port);
                $running = 1;
                break;
            default:
                //writable actions instead of current status, 1 is start 2 is clear last data
                $running = 1;

                return "writable action, shouldn't be happening right now";
                break;
        }

        $running = 1;

        do
        {
            sleep(3);
            $currentAction = $ciscoSwitch->getSnmpTdrIfAction($request->ip, $request->port);
            switch ($currentAction['response'])
            {
                case 3:
                    $running = 1;
                    sleep(1);
                    // echo 'test is still running<br>';
                    break;
                case 4:
                    $running = 0;
                    // echo 'Test is NOT running now maybe have results?<br>';
                    break;
                default:
                    return 'no fonking clue';
                    break;
            }
        } while ($running == 1);

        do
        {
            sleep(3);
            $running=0;
            $currentAction = $ciscoSwitch->getSnmpTdrIfActionStatus($request->ip, $request->port);
            switch ($currentAction['response'])
            {
                case 1:
                    // echo "results should be valid<br>";
                    $running=0;
                    break;
                case 2:
                    //  echo "failed reason unknown<br>";
                    return 'something bad happened<br>';
                case 3:
                    // echo "failed Resource Invalid<br>";
                    return 'something bad happened<br>';
                case 4:
                    // echo "failed Interal Error<br>";
                    return 'something bad happened<br>';
                case 5:
                    // echo "failed Test Already Running<br>";
                    sleep(2);
                    break;
                case 6:
                    // echo 'Failed Interface Disabled<br>';
                    return 'something bad happened';
                default:
                    // echo 'This should never happen<br>';
                    return 'super fail<br>';
            }
        } while ($running == 1);




        $resultvalid = $ciscoSwitch->getSnmpTdrIfResultValid($request->ip, $request->port);
        $running = 1;
        $loopcount = 0;
        $resultTable = [];
        do
        {
            switch ($resultvalid['response'])
            {
                case '1':
                    //success ready to display
                    //echo 'test results? <br>';
                    $running = 0;
                    $resultTable['channel'] = $ciscoSwitch->getSnmpTdrIfResultPairChannel($request->ip, $request->port);
                    $resultTable['length'] = $ciscoSwitch->getSnmpTdrIfResultPairLength($request->ip, $request->port);
                    $resultTable['lenAccuracy'] = $ciscoSwitch->getSnmpTdrIfResultPairLenAccuracy($request->ip, $request->port);
                    $resultTable['faultDistance'] = $ciscoSwitch->getSnmpTdrIfResultPairDistToFault($request->ip, $request->port);
                    $resultTable['pairStatus'] = $ciscoSwitch->getSnmpTdrIfResultPairStatus($request->ip, $request->port);
                    $resultTable['lenUnit'] = $ciscoSwitch->getSnmpTdrIfResultPairLengthUnit($request->ip, $request->port);
                    //dd($pairChannel, $pairLength, $pairLengthAccuracy, $distanceToFault, $pairStatus, $lengthUnit);

                    break;

                default:
                    sleep(2);
                    if ($loopcount > 10)
                    {
                        return 'timeout';
                    }
                    $loopcount ++;
                    // echo 'test is running still? Result not Yet Valid. <br>';
                    $resultvalid = $ciscoSwitch->getSnmpTdrIfResultValid($request->ip, $request->port);
                    $running = 1;
                    break;
            }
        } while ($running == 1);




        return $resultTable;
    }

    public function getSwitchPortAndNeighborInfoTable(Request $request)
    {

        $input = $request->all();
        $switchIp = $input['ip'];

        $sipNetwork = new SIPNetwork();
        $portInfoTable = $sipNetwork->getSwitchPortInfoTable($switchIp);
        $neighborInfoTable = $sipNetwork->getSwitchCdpNeighborInfoTable($switchIp);

        return [$portInfoTable, $neighborInfoTable];
    }

    public function getAvailableSwitchPorts(Request $request)
    {
        $input = $request->all();
        $switchIp = $input['ip'];
        $skipLabelPattern = ['/.*[uU]plink.*/i', '/.*[dD]ownlink.*/i'];
        $sipNetwork = new SIPNetwork();
        return $portInfoTable = $sipNetwork->getSwitchPortInfoTable($switchIp, $skipLabelPattern);

    }


    ######################################
    # Old function - need to be updated
    ######################################

    public function getRouterInfoByPortID(Request $request)
    {
        $input = $request->all();
        $portID = $input['portid'];

        return $this->getRouterByPortID($portID);
    }

    protected function getRouterByPortID($portID, $idCustomer)
    {

        print '<pre>';
        print_r($portID . ' ' . $idCustomer);
        die();

        $servicePort = DataServicePort::with('networkNode')
            ->where('PortID', $portID)
            ->first();
        $netNode = $servicePort->getRelationValue('networkNode');

        $customer = new Customer;
        $customerNetData = $customer->getNetworkNodes($idCustomer)[0];

        return networkNodes::where('LocID', $netNode->LocID)
            ->where('Type', 'Router')
            ->where('Role', 'Master')
            ->first();
    }

    protected function getActiveLeasesOnPort($portID, $comment = '', $idCustomer)
    {
        $userIPInfoArr = array();

        //        print '<pre>';
        //        print_r($idCustomer);
        //        die();

        //        $servicePort = DataServicePort::with('networkNode')
        //            ->where('PortID',$portID)
        //            ->first();
        //        $netNode = $servicePort->getRelationValue('networkNode');

        $customer = new Customer;
        $customerNetData = $customer->getNetworkNodes($idCustomer)[0];


        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $customerNetData->ip_address;
        $switchPort = $customerNetData->port_number;
        $switch = $this->getSwitchInstance();
        $routerNode = $this->getRouterByPortID($portID, $idCustomer);
        $router = $this->getRouterInstance();
        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
        $userIPInfoArr = $router->getDHCPLeasesBySwitchPort($routerIP, $switchIP, $switchPort);

        if ($comment != '')
        {
            $ipByCommentArray = $router->getDHCPLeasesByComment($routerIP, $comment);
            if (count($ipByCommentArray) > 0)
            {
                $userIPInfoArr = array_merge($userIPInfoArr, $ipByCommentArray);
            }
        }

        return $userIPInfoArr;
    }

    protected function getAllLeasesOnPort($routerIP, $switchIP, $switchPort)
    {
        $userIPInfoArr = array();
        $router = $this->getRouterInstance();
        $userIPInfoArr = $router->getAllDHCPLeasesBySwitchPort($routerIP, $switchIP, $switchPort);

        return $userIPInfoArr;
    }

    //    protected function getRouterInstance(){
    //        return new MtikRouter(['username' => $this->mtikusername,
    //                               'password' => $this->mtikpassword]);
    //    }
    //
    //    public function getCustomerConnectionInfo($portID) {
    //        $servicePort = DataServicePort::with('networkNode')->where('id',$portID)->first();
    //
    //        Log::info('port info: ', print_r($servicePort, true));
    //
    //        $netNode = $servicePort->getRelationValue('networkNode');
    //        return ['Name'    => $netNode->host_name,
    //                'IP'      => $netNode->ip_address,
    //                'Port'    => $servicePort->port_number,
    //                'Access'  => $servicePort->access_level,
    //                'Vendor'  => $netNode->vendor,
    //                'Model'   => $netNode->model
    //               ];
    //    }

    //    public function getPortActiveIPs(Request $request)
    //    {
    //        $input = $request->all();
    //        $portID = $input['portid'];
    //        return $this->getActiveLeasesOnPort($portID, null, $input['id']);
    //    }
    //
    //    public function getPortAllIPs(Request $request)
    //    {
    //        $input = $request->all();
    //        $portID = $input['portid'];
    //
    //        //$servicePort = dataServicePort::with('networkNode')
    //        //->where('PortID',$portID)
    //        //->first();
    //        //$netNode = $servicePort->getRelationValue('networkNode');
    //
    //        $customer = new Customer;
    //        $customerNetData = $customer->getNetworkNodes($request->id)[0];
    //
    //
    //        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $customerNetData->ip_address;
    //        $switchPort = $customerNetData->port_number;
    //        $routerNode = $this->getRouterByPortID($portID);
    //        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
    //        return $this->getAllLeasesOnPort($routerIP, $switchIP, $switchPort);
    //    }

    //    public function removeLease(Request $request) {
    //        $input = $request->all();
    //        $portID = $input['portid'];
    //        $leaseID = $input['leaseID'];
    //
    //        $routerNode = $this->getRouterByPortID($portID);
    //        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
    //        $router = $this->getRouterInstance();
    //        $routerActionResult = $router->removeUserDHCPLeaseByID($leaseID, $routerIP);
    //        return array('Status' => 'Removed');
    //    }
    //
    //    public function reserveLease(Request $request) {
    //        $input = $request->all();
    //        $leaseID = $input['leaseID'];
    //        $portID = $input['portid'];
    //        $CID = $input['CID'];
    //
    //        $servicePort = dataServicePort::with('networkNode')
    //            ->where('PortID',$portID)
    //            ->first();
    //
    //        $netNode = $servicePort->getRelationValue('networkNode');
    //        $switchIP = ($this->devMode) ? $this->devModeSwitchIP : $netNode->IPAddress;
    //        $routerNode = $this->getRouterByPortID($portID);
    //        $routerIP = ($this->devMode) ? $this->devModeRouterIP : $routerNode->IPAddress;
    //
    //        $customer = Customers::where('CID',$CID)
    //            ->first();
    //        $LocCode = $customer->LocCode;
    //        $UnitNumber = $customer->UnitNumber;
    //        $portOperStatus = false;
    //
    //        $router = $this->getRouterInstance();
    //        $routerActionResult = $router->reserveUserDHCPLeaseByID($leaseID, $LocCode, $UnitNumber, $routerIP);
    //
    //        return array('Status' => 'Reserved');
    //    }


    //    public function formatSnmpResponse($snmpResponse) {
    //        if ($snmpResponse != '') {
    //            $snmpRespStr = preg_replace('/.+:/', '', $snmpResponse);
    //            return trim($snmpRespStr);
    //        }
    //        return false;
    //    }

}
