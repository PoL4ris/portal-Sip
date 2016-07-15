<?php

namespace App\Extensions;

use App\Models\NetworkNode;
use App\Extensions\CiscoSwitch;
use App\Extensions\RouterOsAPI;

class MtikRouter {

    private $router = null;
    protected $username = '';
    protected $password = '';

    /**
     * @param $props - Array of router params
     *        e.g. IP => 38.126.25.1
     * 	      MAC => 00:00:00:00:00
     * 	      HOSTNAME => chi-mp4-rtr 
     */
    public function __construct($props = NULL) {
        if ($props != NULL) {
            $this->username = isset($props['username']) ? $props['username'] : '';
            $this->password = isset($props['password']) ? $props['password'] : '';
            $ip = isset($props['IPAddress']) ? $props['IPAddress'] : NULL;
            $mac = isset($props['MacAddress']) ? $props['MacAddress'] : NULL;
            $hostName = isset($props['HostName']) ? $props['HostName'] : NULL;
            //            if($ip != NULL && $mac != NULL && $hostName)
            $this->loadFromDB($ip, $mac, $hostName);
        }
    }

    public function loadFromDB($ip = NULL, $mac = NULL, $hostName = NULL) { 
        $array = array();
        $dbRecord = null;
        
        if ($ip) {
            $array ['ip_address'] = $ip;
        }
        if ($mac) {
            $array ['mac_address'] = $mac;
        }
        if ($hostName) {
            $array ['host_name'] = $hostName;
        }

        if (count($array) > 0) {
            $netNodeQuery = NetworkNode::where('id_types',7)
                ->where('role','Master');

            foreach($array as $col => $value){
                $netNodeQuery->where($col,$value);
            }

            $netNode = $netNodeQuery->first();

            if ($netNode != null) {
                foreach ($netNode->toArray() as $key => $value) {
                    $this->router[$key] = $value;
                }
                $this->router['selected'] = true;
            } else {
                $this->router = null;
                $this->selected = false;
            }
        }
        return $this;
    }

    public function getRouterObject() {
        return $this->router;
    }

    protected function getSwitchInstance(){
        return new CiscoSwitch(['readCommunity' => config('netmgmt.cisco.read'),
                                'writeCommunity' => config('netmgmt.cisco.write')]);
    }

    public function register($ipAddressList, $location = NULL) {
        if (isset($ipAddressList) == false && count($ipAddressList) <= 0) {
            return false;
        }
        foreach ($ipAddressList as $ip) {
            $hostName = $this->getHostName($ip);
            if ($this->isRegistered($ip, null, $hostName) == false) {
                $netNode = new NetworkNode;
                $netNode->ip_address = $ip;
                $netNode->host_name = $hostName;
                if ($location != NULL) {
                    $netNode->id_address =  $location;
                }
                $netNode->id_types = 7;
                $netNode->vendor = 'Mikrotik';
                $netNode->save();
            }
        }
    }

    public function getHostName($ip = NULL) {
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip)) {
            $API = new RouterOsAPI();
            if (isset($this->router['debug']) && $this->router['debug'] == true) {
                $API->debug = true;
            }

            if ($API->connect($ip, $this->username, $this->password)) {
                $apiQueryResult = $API->comm('/system/identity/print');
                $API->disconnect();
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    return $apiQueryResult[0]['name'];
                }
            }
        }
        return null;
    }

    public function getIpPoolRange($poolName, $ip = NULL) {
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip) && isset($poolName)) {
            $API = new RouterOsAPI();
            if (isset($this->router['debug']) && $this->router['debug'] == true) {
                $API->debug = true;
            }

            if ($API->connect($ip, $this->username, $this->password)) {

                $ipPoolSearchConditionsArray = array();
                $ipPoolSearchConditionsArray ['?name'] = $poolName;
                $apiQueryResult = $API->comm('/ip/pool/print', $ipPoolSearchConditionsArray);

                $API->disconnect();
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    return $apiQueryResult;
                }
            }
        }
        return null;
    }

    public function getIpPoolSize($poolName, $ip = NULL) {
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip) && isset($poolName)) {
            $ipPoolInfo = self::getIpPoolRange($poolName, $ip);
            if (isset($ipPoolInfo) && count($ipPoolInfo > 0)) {
                $ranges = $ipPoolInfo[0]['ranges'];
                $rangeArr = explode(',', $ranges);
                $totalIPs = 0;
                foreach ($rangeArr as $range) {
                    $rangeList = explode('-', $range);
                    if (count($rangeList) > 1) {
                        $startingIP = explode('.', $rangeList[0]);
                        $endingIP = explode('.', $rangeList[1]);
                        $startingNumb = $startingIP[3];
                        $endingNumb = $endingIP[3];
                        $totalIPs += $endingNumb - $startingNumb;
                    } else {
                        $totalIPs += 1;
                    }
                }

                return $totalIPs;
            }
        }
        return false;
    }

    public function getActiveDHCPLeaseCountByServer($serverName, $ip = NULL) {
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip)) {
            $API = new RouterOsAPI();

            if ($this->router != null && isset($this->router['debug']) && $this->router['debug'] == true) {
                $API->debug = true;
            }
            //            $API->debug = true;

            if ($API->connect($ip, $this->username, $this->password)) {
                $apiQueryArray = array();
                $apiQueryArray ['count-only'] = '';
                $apiQueryArray ['?server'] = $serverName;
                $apiQueryArray ['?disabled'] = 'no';
                $apiQueryResult = $API->comm('/ip/dhcp-server/lease/print', $apiQueryArray);

                //                $API->write('/cancel');
                //                debug("done\n");
                //                debug("Reading results ... ");
                //                $ARRAY = $API->read();
                //                print_r($apiQueryResult);

                $API->disconnect();
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    return $apiQueryResult;
                }
            }
        }
        return null;
    }

    public function getActiveDHCPLeasesByServer($serverName, $ip = NULL) {
        //        error_log('Insde getActiveDHCPLeasesByServer($serverName): $serverName = '.$serverName);
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip)) {
            $API = new RouterOsAPI();

            if ($this->router != null && isset($this->router['debug']) && $this->router['debug'] == true) {
                $API->debug = true;
            }
            //            $API->debug = true;

            if ($API->connect($ip, $this->username, $this->password)) {
                $apiQueryArray = array();
                //                $apiQueryArray ['count-only'] = '';
                $apiQueryArray ['?server'] = $serverName;
                $apiQueryArray ['?disabled'] = 'no';
                $apiQueryResult = $API->comm('/ip/dhcp-server/lease/print', $apiQueryArray);

                //                $API->write('/cancel');
                //                debug("done\n");
                //                debug("Reading results ... ");
                //                $ARRAY = $API->read();
                //                print_r($apiQueryResult);

                $API->disconnect();
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    return $apiQueryResult;
                }
            }
        }
        return null;
    }

    public function getPublicIpPoolSize($ip = NULL) {
        return self::getIpPoolSize('public-pool', $ip);
    }

    public function getDHCPLeases($ip = NULL) {
        if (!isset($ip)) {
            if ($this->isSelected()) {
                $ip = $this->router['IPAddress'];
            }
        }

        if (isset($ip)) {
            $API = new RouterOsAPI();
            if (isset($this->router['debug']) && $this->router['debug'] == true) {
                $API->debug = true;
            }

            if ($API->connect($ip, $this->username, $this->password)) {
                $apiQueryResult = $API->comm('/ip/dhcp-server/lease/print');
                $API->disconnect();
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    return $apiQueryResult;
                }
            }
        }
        return null;
    }

    public function reserveUserDHCPLeaseByID($id, $locCode, $unit, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $ipBindingArray = array();
                $ipBindingArray ['.id'] = $id;
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/make-static', $ipBindingArray);
                sleep(2);
                $ipBindingArray['comment'] = $locCode . ' #' . $unit;
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/set', $ipBindingArray);
                $API->disconnect();
            }
        }
    }

    public function removeUserDHCPLeaseByID($id, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $ipBindingArray = array();
                $ipBindingArray ['.id'] = $id;
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/remove', $ipBindingArray);
                $API->disconnect();
            }
        }
    }

    public function disableUserDHCPLeaseByID($id, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $ipBindingArray = array();
                $ipBindingArray ['.id'] = $id;
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/make-static', $ipBindingArray);
                sleep(2);
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/disable', $ipBindingArray);
                $API->disconnect();
            }
        }
    }

    public function enableUserDHCPLeaseByID($id, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $ipBindingArray = array();
                $ipBindingArray ['.id'] = $id;
                $apiSendResult = $API->comm('/ip/dhcp-server/lease/enable', $ipBindingArray);
                $API->disconnect();
            }
        }
    }

    public function resetUserMacAddress($mac) {

        if ($this->isSelected()) {
            $ip = $this->router['IPAddress'];
            $API = new RouterOsAPI();
            if ($API->connect($ip, $this->username, $this->password)) {

                $apiQueryArray [".proplist"] = '.id';
                $apiQueryArray ["?mac-address"] = strtoupper($mac);
                $apiQueryResult = $API->comm('/ip/hotspot/host/print', $apiQueryArray);
                //                error_log('$apiQueryResult '. print_r($apiQueryResult,true));

                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    foreach ($apiQueryResult as $key) {
                        //                        error_log('removing .id '. $key['.id']);
                        $idArray = array();
                        $idArray [".id"] = $key['.id'];
                        $apiSendResult = $API->comm('/ip/hotspot/host/remove', $idArray);
                    }
                }
                $API->disconnect();
            }
        }
    }

    public function authUserByMacAddress($mac) {

        if ($this->isSelected()) {
            $ip = $this->router['IPAddress'];
            $API = new RouterOsAPI();
            if ($API->connect($ip, $this->username, $this->password)) {
                $ipBindingArray = array();
                $ipBindingArray ["mac-address"] = strtoupper($mac);
                $ipBindingArray ["type"] = 'bypassed';

                $apiSendResult = $API->comm('/ip/hotspot/ip-binding/add', $ipBindingArray);

                $apiQueryArray [".proplist"] = '.id';
                $apiQueryArray ["?mac-address"] = strtoupper($mac);
                $apiQueryResult = $API->comm('/ip/hotspot/ip-binding/print', $apiQueryArray);

                if (isset($apiQueryResult) && count($apiQueryResult) > 0 && isset($apiQueryResult[0]['.id'])) {
                    $ipBindingArray = array();
                    $ipBindingArray [".id"] = $apiQueryResult[0]['.id'];
                    $apiSendResult = $API->comm('/ip/hotspot/ip-binding/remove', $ipBindingArray);
                }

                $API->disconnect();
            }
        }
    }

    public function disableUserDHCPLease($ip, $mac, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {

                $apiQueryArray = array();
                $apiQueryArray ["?address"] = $ip;
                $apiQueryArray ["?mac-address"] = strtoupper($mac);

                $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);

                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    $ipBindingArray = array();
                    $ipBindingArray ['.id'] = $apiQueryResult[0]['.id'];
                    $apiSendResult = $API->comm('/ip/dhcp-server/lease/make-static', $ipBindingArray);
                    sleep(2);
                    $apiSendResult = $API->comm('/ip/dhcp-server/lease/disable', $ipBindingArray);
                }
                $API->disconnect();
            }
        }
    }

    public function enableUserDHCPLease($ip, $mac, $routerIp = NULL) {
        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {

                $apiQueryArray = array();
                $apiQueryArray ["?address"] = $ip;
                $apiQueryArray ["?mac-address"] = strtoupper($mac);

                $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);

                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    $ipBindingArray = array();
                    $ipBindingArray ['.id'] = $apiQueryResult[0]['.id'];
                    $apiSendResult = $API->comm('/ip/dhcp-server/lease/enable', $ipBindingArray);
                }
                $API->disconnect();
            }
        }
    }

    /*
     * Returns the user's DHCP lease from the switch ip and port number
     */

    public function getDHCPLeasesBySwitchPort($routerIp, $switchIP, $switchPort) {
        //        error_log('getDHCPLeasesBySwitchPort(): called');
        //      agent-circuit-id="0:4:0:e9:1:b" agent-remote-id="0:6:0:24:f9:68:93:0"            
        $userIPInfo = array();
        if ($routerIp != '' && $switchIP != '' && $switchPort != '') {
            $switch = $this->getSwitchInstance();
            $switchModel = $switch->getSnmpModelNumber($switchIP);
            $switchMAC = $switch->getSnmpMacAddress($switchIP);
            $portVlans = $switch->getSnmpPortVlanAssignment($switchIP, $switchPort);

            foreach ($portVlans as $portVlan) {
                //                error_log('mikrotikRouter::getDHCPLeasesBySwitchPort(): $portVlan = ' . $portVlan);
                $apiQueryArray = array();
                $portVlanHex = strtolower(dechex($portVlan));
                $switchPortHex = '';
                if ($switchModel != false && strstr($switchModel, 'WS-C2950')) {
                    $switchPortHex = strtolower(dechex($switchPort - 1));
                    $agentCircuitId = '0:4:0:' . $portVlanHex . ':0:' . $switchPortHex;
                } else {
                    $switchPortHex = strtolower(dechex($switchPort));
                    $agentCircuitId = '0:4:0:' . $portVlanHex . ':1:' . $switchPortHex;
                }

                $remoteId = strtolower($switchMAC);
                $remoteIDArray = explode(':', $remoteId);
                foreach ($remoteIDArray as $key => $oct) {
                    $remoteIDArray[$key] = preg_replace('/^0/', '', $oct);
                }
                $agentRemoteId = '0:6:' . implode(':', $remoteIDArray);
                $apiQueryArray ["?disabled"] = 'no';
                $apiQueryArray ["?agent-circuit-id"] = $agentCircuitId;
                $apiQueryArray ["?agent-remote-id"] = $agentRemoteId;

                $API = new RouterOsAPI();
                if ($API->connect($routerIp, $this->username, $this->password)) {
                    $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);
                    if (!isset($apiQueryResult) || count($apiQueryResult) == 0) {
                        return $userIPInfo;
                    } else if (isset($apiQueryResult) && count($apiQueryResult) > 0) {

                        foreach ($apiQueryResult as $dhcpEntry) {
                            $userIPInfo[] = $dhcpEntry;
                        }
                    }
                    $API->disconnect();
                }

            }
        }
        //        error_log('mikrotikRouter::getDHCPLeasesBySwitchPort(): $userIPInfo = ' . print_r($userIPInfo, true));
        return $userIPInfo;
    }

    /*
     * Returns the user's DHCP lease from the switch ip and port number
     */

    public function getDHCPLeasesByComment($routerIp, $comment) {
        //      agent-circuit-id="0:4:0:e9:1:b" agent-remote-id="0:6:0:24:f9:68:93:0"            
        $apiQueryArray = array();
        $userIPInfo = array();
        if ($routerIp != '' && $comment != '') {
            $apiQueryArray ["?disabled"] = 'no';
            $apiQueryArray ["?comment"] = $comment;
            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);
                if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                    foreach ($apiQueryResult as $dhcpEntry) {
                        $userIPInfo[] = $dhcpEntry;
                    }
                }
            }
            $API->disconnect();
        }
        return $userIPInfo;
    }

    public function getAllDHCPLeasesBySwitchPort($routerIp, $switchIP, $switchPort) {
        //      agent-circuit-id="0:4:0:e9:1:b" agent-remote-id="0:6:0:24:f9:68:93:0"            
        $userIPInfo = array();
        if ($routerIp != '' && $switchIP != '' && $switchPort != '') {
            $switch = $this->getSwitchInstance();
            $switchModel = $switch->getSnmpModelNumber($switchIP);
            $switchMAC = $switch->getSnmpMacAddress($switchIP);
            $portVlan = $switch->getSnmpPortVlanAssignment($switchIP, $switchPort);
            $apiQueryArray = array();

            $portVlanHex = strtolower(dechex($portVlan));
            $switchPortHex = '';
            if ($switchModel != false && strstr($switchModel, 'WS-C2950')) {
                $switchPortHex = strtolower(dechex($switchPort - 1));
                $agentCircuitId = '0:4:0:' . $portVlanHex . ':0:' . $switchPortHex;
            } else {
                $switchPortHex = strtolower(dechex($switchPort));
                $agentCircuitId = '0:4:0:' . $portVlanHex . ':1:' . $switchPortHex;
            }

            $remoteId = strtolower($switchMAC);
            $remoteIDArray = explode(':', $remoteId);
            foreach ($remoteIDArray as $key => $oct) {
                $remoteIDArray[$key] = preg_replace('/^0/', '', $oct);
            }
            $agentRemoteId = '0:6:' . implode(':', $remoteIDArray);
            //            $apiQueryArray ["?disabled"] = 'no';
            $apiQueryArray ["?agent-circuit-id"] = $agentCircuitId;
            $apiQueryArray ["?agent-remote-id"] = $agentRemoteId;

            $API = new RouterOsAPI();
            if ($API->connect($routerIp, $this->username, $this->password)) {
                $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);
                if (!isset($apiQueryResult) || count($apiQueryResult) == 0) {
                    return false;
                } else if (isset($apiQueryResult) && count($apiQueryResult) > 0) {

                    foreach ($apiQueryResult as $dhcpEntry) {
                        $userIPInfo[] = $dhcpEntry;
                    }
                }
            }
            $API->disconnect();
        }
        return $userIPInfo;
    }

    /*
     *  Returns the switch port info that the user is connected to 
     *  (based on the user's $ip and/or $mac)
     */

    public function getUserPortInfo($routerIp = NULL, $ip = NULL, $mac = NULL) {
        $apiQueryArray = array();
        $userPortInfo = array();
        $userSession = Session::getInstance();
        $userSession->universalNat = false;
        $userSession->invalidUserIP = false;
        //        error_log('MtikRouter.php: getUserPortInfo(): called');

        if (!isset($routerIp)) {
            if ($this->isSelected()) {
                $routerIp = $this->router['IPAddress'];
            }
        }

        if (isset($routerIp)) {
            if ($ip) {
                $apiQueryArray ["?address"] = $ip;
            }
            if ($mac) {
                $apiQueryArray ["?mac-address"] = strtoupper($mac);
            }

            if ($ip || $mac) {
                $API = new RouterOsAPI();
                if ($API->connect($routerIp, $this->username, $this->password)) {
                    $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);
                    if (!isset($apiQueryResult) || count($apiQueryResult) == 0) {

                        // Could not find the dhcp lease. Check the universal NAT binding table
                        unset($apiQueryArray ["?address"]);
                        $apiQueryResult = $API->comm("/ip/hotspot/host/print", $apiQueryArray);
                        if (isset($apiQueryResult) && count($apiQueryResult) > 0) {
                            // Found the user in universal NAT. Store the universal nat data
                            $userPortInfo['Uni-IPAddress'] = $apiQueryResult[0]['address'];
                            $userPortInfo['Uni-MacAddress'] = $apiQueryResult[0]['mac-address'];
                            $userPortInfo['Uni-ToIPAddress'] = $apiQueryResult[0]['to-address'];

                            // Now go back and find the dhcp lease
                            $userSession->universalNat = true;
                            $apiQueryArray ['?address'] = $apiQueryResult[0]['address'];
                            $apiQueryResult = $API->comm("/ip/dhcp-server/lease/print", $apiQueryArray);
                        }
                    }

                    if (isset($apiQueryResult) && count($apiQueryResult) > 0) {

                        if (isset($apiQueryResult[0]['agent-remote-id']) && count($apiQueryResult[0]['agent-remote-id']) > 0) {
                            // Format MAC Address
                            $agentRemoteID = $apiQueryResult[0]['agent-remote-id'];
                            $remoteIDArray = explode(':', $agentRemoteID);
                            array_shift($remoteIDArray);
                            array_shift($remoteIDArray);
                            foreach ($remoteIDArray as $key => $oct) {
                                if (strlen($oct) == 1) {
                                    $remoteIDArray[$key] = '0' . $remoteIDArray[$key];
                                }
                            }
                            $userPortInfo['Switch MAC Address'] = implode(':', $remoteIDArray);
                            $userPortInfo['Switch MAC Address'] = strtoupper($userPortInfo['Switch MAC Address']);
                        } else {
                            // No switch info found in the DHCP Lease
                            $userPortInfo['Switch MAC Address'] = NULL;
                        }

                        if (isset($apiQueryResult[0]['agent-circuit-id']) && count($apiQueryResult[0]['agent-circuit-id']) > 0) {
                            // Get port number and VLAN
                            $agentCircuitID = $apiQueryResult[0]['agent-circuit-id'];
                            $circuitIDArray = explode(':', $agentCircuitID);
                            $userPortInfo['Switch Port Number'] = hexdec(array_pop($circuitIDArray)); // + 1;
                            $userPortInfo['Switch Instance ID'] = hexdec(array_pop($circuitIDArray));
                            $userPortInfo['Port VLAN'] = hexdec(array_pop($circuitIDArray));
                        } else {
                            $userPortInfo['Switch Port Number'] = NULL;
                            $userPortInfo['Switch Instance ID'] = NULL;
                            $userPortInfo['Port VLAN'] = NULL;
                        }

                        $userPortInfo['IPAddress'] = ($apiQueryResult[0]['address']) ? $apiQueryResult[0]['address'] : $userPortInfo['Uni-IPAddress'];
                        $userPortInfo['MacAddress'] = ($apiQueryResult[0]['mac-address']) ? $apiQueryResult[0]['mac-address'] : $userPortInfo['Uni-MacAddress'];
                    } else {
                        // User must have a static IP set
                        if ($userSession->universalNat) {
                            $userPortInfo['IPAddress'] = $userPortInfo['Uni-IPAddress'];
                            $userPortInfo['MacAddress'] = $userPortInfo['Uni-MacAddress'];
                        } else {
                            $userPortInfo['IPAddress'] = NULL;
                            $userPortInfo['MacAddress'] = NULL;
                        }

                        $userPortInfo['Switch MAC Address'] = NULL;
                        $userPortInfo['Switch Port Number'] = NULL;
                        $userPortInfo['Switch Instance ID'] = NULL;
                        $userPortInfo['Port VLAN'] = NULL;
                        $userSession->invalidUserIP = true;
                    }

                    $API->disconnect();
                }
            }
        }
        //        error_log('MtikRouter.php: getUserPortInfo(): $userSession->invalidUserIP = '.$userSession->invalidUserIP. ' $userSession->universalNat = '.$userSession->universalNat);
        return $userPortInfo;
    }

    protected function isRegistered($ip = NULL, $mac = NULL, $hostName = NULL) {
        $routerInDB = $this->loadFromDB($ip, $mac, $hostName);
        if ($routerInDB->isSelected()) {
            return true;
        }
        return false;
    }

    public function isSelected() {
        return $this->router['selected'];
    }

    public function __set($name, $value) {
        $this->router[$name] = $value;
    }

    public function __get($name) {
        if (isset($this->router[$name])) {
            return $this->router[$name];
        }
    }

    public function __isset($name) {
        return isset($this->router[$name]);
    }

    public function __unset($name) {
        unset($this->router[$name]);
    }

}

?>