<?php

namespace App\Extensions;

use App\Models\NetworkNode;

class CiscoSwitch {

    //***  Sample class variables... 
    //    const SESSION_STARTED = TRUE;
    //    const SESSION_NOT_STARTED = FALSE;
    //    private $sessionState = self::SESSION_NOT_STARTED;   
    //***  THE only instance of the class
    //    private static $instance;

    private $switch = null;
    //    private $ipAddress;
    //    private $model;
    private $portIndexList;
    private $bridgePortIndexList;

    //    protected $portIndexQueryStr = 'ifDescr';
    // If you do not have any loaded MIBs then use the following as fallback	
    const portIndexQueryStr = '1.3.6.1.2.1.2.2.1.2';

    const sysName = '1.3.6.1.2.1.1.5';
    const sysLocation = '1.3.6.1.2.1.1.6';
    //  system.sysUpTime.0
    const sysUpTime = '1.3.6.1.2.1.1.3';
    const ifAlias = '1.3.6.1.2.1.31.1.1.1.18';
    const ifSpeed = '1.3.6.1.2.1.2.2.1.5';
    const ifAdminStatus = '1.3.6.1.2.1.2.2.1.7';
    const ifOperStatus = '1.3.6.1.2.1.2.2.1.8';
    //    const ifPortFast = '1.3.6.1.4.1.9.5.1.4.1.1.12.1'; // 1: enabled, 2: disabled
    const ifPortFast = '1.3.6.1.4.1.9.9.82.1.9.3.1.2'; // 1: enabled, 2: disabled
    const ifPortFastMode = '1.3.6.1.4.1.9.9.82.1.9.3.1.3'; // 1: enabled, 2: disabled, 3: enable for trunk, 4: default
    const ifbpduGuard = '1.3.6.1.4.1.9.9.82.1.9.3.1.4'; // 1: enabled, 2: disabled, 3: default
    const ifbpduFilter = '1.3.6.1.4.1.9.9.82.1.9.3.1.5'; // 1: enabled, 2: disabled, 3: default
    const ifModeOper = '1.3.6.1.4.1.9.9.151.1.1.1.1.2'; // 1: routed, 2: switchport 
    //    const ifswitchportMode = '1.3.6.1.4.1.9.5.1.9.3.1.8.1'; // 1: trunk, 2: not trunk (use bridge index not ifindex)
    const ifswitchportMode = '1.3.6.1.4.1.9.9.46.1.6.1.1.14'; // 1: trunk, 2: not trunk (use ifIndex)
    const trunk = 1;
    const access = 2;
    const dot1dBasePortIfIndex = '1.3.6.1.2.1.17.1.4.1.2';
    const vlanTrunkPortVlansEnabled = '1.3.6.1.4.1.9.9.46.1.6.1.1.4';
    const vlanTrunkPortEncapsulationType = '1.3.6.1.4.1.9.9.46.1.6.1.1.3';   // 1: isl, 2: dot10, 3: lane ,4: dot1Q, 5: negotiate
    const vlanTrunkPortNativeVlan = '1.3.6.1.4.1.9.9.46.1.6.1.1.5';   // Native vlan on a trunk port
    const ifLastChange = '1.3.6.1.2.1.2.2.1.9';
    const ifInOctets = '1.3.6.1.2.1.2.2.1.10';
    const ifOutOctets = '1.3.6.1.2.1.2.2.1.16';
    const ifInDiscards = '1.3.6.1.2.1.2.2.1.13';
    const ifOutDiscards = '1.3.6.1.2.1.2.2.1.19';
    const ifInErrors = '1.3.6.1.2.1.2.2.1.14';
    const ifOutErrors = '1.3.6.1.2.1.2.2.1.20';
    const ifInBroadcastPkts = '1.3.6.1.2.1.31.1.1.1.3';
    const ifOutBroadcastPkts = '1.3.6.1.2.1.31.1.1.1.5';
    const dot1dBaseBridgeAddress = '1.3.6.1.2.1.17.1.1';
    //    const entPhysicalDescr = '1.3.6.1.2.1.47.1.1.1.1.2';
    const entPhysicalDescr = '1.3.6.1.2.1.47.1.1.1.1.13'; //.1001';
    const entPhysicalFirmwareRev = '1.3.6.1.2.1.47.1.1.1.1.9';
    const vmVlan = '1.3.6.1.4.1.9.9.68.1.2.2.1.2';

    /**
     *  New OIDs 
     */
    const cpmProcessPID = '1.3.6.1.4.1.9.9.109.1.2.1.1.1';   // cpmProcessPID: running processe IDs
    const cpmProcessName = '1.3.6.1.4.1.9.9.109.1.2.1.1.2';  //  cpmProcessName: running processes
    const vmMembershipSummaryMemberPorts = '1.3.6.1.4.1.9.9.68.1.2.1.1.2'; //    vmMembershipSummaryMemberPorts: The set of the device's member ports that belong to the VLAN"
    const vtpVlanName = '1.3.6.1.4.1.9.9.46.1.3.1.1.4.1';       //  vtpVlanName: "The name of this VLAN. This name is used as the ELAN-name for an ATM LAN-Emulation segment of this VLAN."
    const cdpCacheVersion = '1.3.6.1.4.1.9.9.23.1.2.1.1.5';     // cdpCacheVersion: "The Version string as reported in the most recent CDP message"
    const cdpCacheDeviceId = '1.3.6.1.4.1.9.9.23.1.2.1.1.6';    // cdpCacheDeviceId: "The Device-ID string as reported in the most recent CDP message"
    const cdpCacheDevicePort = '1.3.6.1.4.1.9.9.23.1.2.1.1.7';    // cdpCacheDevicePort: "The Port-ID string as reported in the most recent CDP message"
    const cdpCachePlatform = '1.3.6.1.4.1.9.9.23.1.2.1.1.8';    // cdpCachePlatform: "The Device's Hardware Platform as reported in the most recent CDP message"
    const cdpCacheCapabilities = '1.3.6.1.4.1.9.9.23.1.2.1.1.9';    // cdpCacheCapabilities: "The Device's Functional Capabilities as reported in the most recent CDP message"
    const ciscoEnvMonSupplyStatusDescr = '1.3.6.1.4.1.9.9.13.1.5.1.2';      // ciscoEnvMonSupplyStatusDescr: "Textual description of the power supply being instrumented"
    const ciscoEnvMonSupplyState = '1.3.6.1.4.1.9.9.13.1.5.1.3';      // ciscoEnvMonSupplyState: "The current state of the power supply being instrumented"
    //        CiscoEnvMonState
    //        1:normal
    //        2:warning
    //        3:critical
    //        4:shutdown
    //        5:notPresent
    //        6:notFunctioning
    const ciscoFlashFileName = '1.3.6.1.4.1.9.9.10.1.1.4.2.1.1.5';     // Walk this to see files on the flash://

    protected $readCommunity = '';
    protected $writeCommunity = '';

    public function __construct($props = null) {
        if ($props != null) {
            if(isset($props['readCommunity'])){
                $this->readCommunity = $props['readCommunity'];
                unset($props['readCommunity']);
            }

            if(isset($props['writeCommunity'])){
                $this->writeCommunity = $props['writeCommunity'];
                unset($props['writeCommunity']);
            }

            if (isset($props['selected'])) {
                $this->switch = array();
                foreach ($props as $key => $value) {
                    $this->switch[$key] = $value;
                }
            }
        }
    }

    public function isRegistered($ip = NULL, $mac = NULL, $hostName = NULL) {
        $switchInDB = $this->loadFromDB($ip, $mac, $hostName);
        if ($switchInDB) {
            return true;
        }
        return false;
    }

    public function register($ipAddressList, $location = NULL) {
        if (isset($ipAddressList) == false && count($ipAddressList) <= 0) {
            return false;
        }
        foreach ($ipAddressList as $ip) {
//            $hostName = str_replace('.silverip.net', '', $this->formatSnmpResponse($this->getSnmpSysName($ip)));
            $hostName = $this->formatSnmpResponse($this->getSnmpSysName($ip));
            $mac = $this->getSnmpMacAddress($ip);
            $model = str_replace('"', '', $this->getSnmpModelNumber($ip));
            $netNode = new NetworkNode;
            $netNode->ip_address = $ip;
            $netNode->mac_address = $mac;
            $netNode->host_name = $hostName;            
            if ($location != NULL) {
                $netNode->id_address =  $location;
            }
            $netNode->id_types = 8;
            $netNode->vendor = 'Cisco';
            $netNode->model = $model;
            
            $netNode->save();
        }
        return true;
    }

    public function loadFromDB($ip = NULL, $mac = NULL, $hostName = NULL) {
        $array = array();
        $dbRecord = null;
        if ($ip) {
            $array ['IPAddress'] = $ip;
        }
        if ($mac) {
            $array ['MacAddress'] = $mac;
        }
        if ($hostName) {
            $array ['HostName'] = $hostName;
        }

        if (count($array) > 0) {
            $netNodeQuery = NetworkNode::where('Type','Switch');

            foreach($array as $col => $value){
                $netNodeQuery->where($col,$value);
            }

            $netNode = $netNodeQuery->first()->toArray();

            if ($netNode) {
                foreach ($netNode as $key => $value) {
                    $this->switch[$key] = $value;
                }
                $this->switch['selected'] = true;
            } else {
                $this->switch = null;
                $this->selected = false;
            }
        }
        return $this;
    }

    public function getSwitchObject() {
        return $this->switch;
    }

    public function getSnmpSysName($ip) {
        if (isset($ip) && $ip != NULL) {
            //            $snmpReults = snmp2_real_walk($ip, $this->readCommunity, 'sysName');
            $snmpReults = snmp2_real_walk($ip, $this->readCommunity, self::sysName);
            $sysName = array_shift($snmpReults);
            return $sysName;
        }
        return '';
    }

    public function getSnmpModelNumber($ip) {
        if (isset($ip) && $ip != NULL) {
            $getModel = false;
            if (!isset($this->ipAddress) || $ip != $this->ipAddress) {
                $this->ipAddress = $ip;
                $getModel = true;
            } elseif (isset($this->model)) {
                return $this->model;
            } else {
                $getModel = true;
            }
        }

        if ($getModel == false) {
            return $getModel;
        }

        $entPhysicalDescrArr = snmp2_real_walk($this->ipAddress, $this->readCommunity, self::entPhysicalDescr);
        //        error_log(print_r($entPhysicalDescrArr,true));

        if (isset($entPhysicalDescrArr) && $entPhysicalDescrArr != false && count($entPhysicalDescrArr) > 0) {
            foreach ($entPhysicalDescrArr as $oid => $entPhysicalDescr) {
                if ($entPhysicalDescr != '""') {
                    $this->model = $this->formatSnmpResponse($entPhysicalDescr);
                    return $this->model;
                }
            }
        }
        return $this->model;
    }

    public function getSnmpSoftwareVersion($ip) {
        if (isset($ip) && $ip != NULL) {
            //            $modelNumber = snmp2_get($ip, $this->readCommunity, 'mib-2.47.1.1.1.1.9.1001');
            $entPhysicalFirmwareRevArr = snmp2_real_walk($ip, $this->readCommunity, self::entPhysicalFirmwareRev);
            if (isset($entPhysicalFirmwareRevArr) && count($entPhysicalFirmwareRevArr) > 0) {
                return array_shift($entPhysicalFirmwareRevArr);
            }
        }
        return false;
    }

    public function getSnmpSysUptime($ip) {
        if (isset($ip) && $ip != NULL) {
            //           $sysUptime = snmp2_get($ip, $this->readCommunity, 'system.sysUpTime.0');
            $sysUptime = snmp2_get($ip, $this->readCommunity, self::sysUpTime . '.0');
            $sysUptimeStr = $lastChangeStr = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $sysUptime);
            return $sysUptimeStr;
        }
        return false;
    }

    public function getSnmpSysUptimeFormatted($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $sysUptime = self::getSnmpSysUptime($ip, $portNum, $isIdx);
            $sysUptimeTimeString = self::getTimeString($sysUptime);
            return $sysUptimeTimeString;
        }
        return false;
    }

    public function getSnmpSysLocation($ip) {
        if (isset($ip) && $ip != NULL) {
            $snmpReults = snmp2_real_walk($ip, $this->readCommunity, self::sysLocation . '.0');
            $sysLocation = $this->formatSnmpResponse(array_shift($snmpReults));
            return $sysLocation;
        }
        return false;
    }

    public function setSnmpSysLocation($ip, $sysLocation) {
        if (isset($ip) && $ip != NULL && isset($sysLocation) && $sysLocation != NULL) {
            if (snmp2_set($ip, $this->writeCommunity, self::sysLocation . '.0', 's', '"' . $sysLocation . '"', '1000000', '5')) {
                return true;
            }
        }
        return false;
    }

    public function setSnmpPortLabel($ip, $portNum, $label, $isIdx = false) {

        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL && isset($label) && $label != NULL) {
            $portIndex = '';
            if (isset($isIdx) && $isIdx == false) {
                $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            } else {
                $portIndex = $portNum;
            }

            if (snmp2_set($ip, $this->writeCommunity, self::ifAlias . '.' . $portIndex, 's', $label, '1000000', '5')) {

                return true;
            }
        }
        return false;
    }

    public function getSnmpMacAddress($ip) {
        if (isset($ip) && $ip != NULL) {
            //            snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
            $sysMacAddress = snmp2_get($ip, $this->readCommunity, self::dot1dBaseBridgeAddress . '.0');

            $sysMacAddress = trim(preg_replace('/^.*STRING:\s+/', '', $sysMacAddress));
            $sysMacAddress = preg_replace('/\s+/', ':', $sysMacAddress);

            //            $sysMacAddressOctets = explode(':',$sysMacAddress);
            //            foreach($sysMacAddressOctets as $key=>$oct){
            //                if(strlen($oct) == 1){ 
            //                    $sysMacAddressOctets[$key] = '0'.$sysMacAddressOctets[$key];
            //                }
            //            }
            //            $sysMacAddress = implode(':',$sysMacAddressOctets);
            $sysMacAddress = strtoupper($sysMacAddress);
            //            snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            return $sysMacAddress;
        }
        return false;
    }

    public function getSnmpPortLabel($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portLabel = snmp2_get($ip, $this->readCommunity, self::ifAlias . '.' . $portIndex);
                return $this->formatSnmpResponse($portLabel);
            }
        }
        return false;
    }

    public function getSnmpAllPortLabel($ip, $keyRegEx = '') {
        return self::getSnmpAllPortsQuery($ip, self::ifAlias, $keyRegEx);
    }

    public function getSnmpAllPortDesc($ip, $keyRegEx = '') {
        if (isset($ip) && $ip != NULL) {
            $portArray = snmp2_real_walk($ip, $this->readCommunity, self::portIndexQueryStr);
            $formattedPortArray = $this->formatSnmpResponse($portArray);
            return self::getMatchingValueEntries($formattedPortArray, $keyRegEx);
        }
        return false;
    }

    public function getSnmpAllPortsQuery($ip, $oid, $keyRegEx = '') {
        if (isset($ip) && $ip != NULL) {
            $filteredPortIndexArr = self::getSnmpAllPortDesc($ip, $keyRegEx);
            $portArr = snmp2_real_walk($ip, $this->readCommunity, $oid);
            $formattedPortArray = $this->formatSnmpResponse($portArr);
            if ($keyRegEx != '') {
                foreach (array_keys($formattedPortArray) as $key) {
                    if (array_key_exists($key, $filteredPortIndexArr) == false) {
                        unset($formattedPortArray[$key]);
                    }
                }
            }
            return $formattedPortArray;
        }
        return false;
    }

    public function getSnmpPortSpeed($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portSpeed = snmp2_get($ip, $this->readCommunity, self::ifSpeed . '.' . $portIndex);
                return $this->formatSnmpResponse($portSpeed);
            }
        }
        return false;
    }

    public function getSnmpAllPortSpeed($ip, $keyRegEx = '') {
        return self::getSnmpAllPortsQuery($ip, self::ifSpeed, $keyRegEx);
    }

    public function getSnmpPortAdminStatus($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portAdminStatus = snmp2_get($ip, $this->readCommunity, self::ifAdminStatus . '.' . $portIndex);
                return $this->formatSnmpResponse($portAdminStatus);
            }
        }
        return false;
    }

    public function getSnmpAllPortAdminStatus($ip, $keyRegEx = '') {
        $portAdmintatusArr = self::getSnmpAllPortsQuery($ip, self::ifAdminStatus, $keyRegEx);
        foreach ($portAdmintatusArr as $key => $adminStatusEntry) {
            $adminStatusStr = preg_replace('/^(.*)\(.*/', '$1', $adminStatusEntry);
            $portAdmintatusArr[$key] = $adminStatusStr;
        }
        return $portAdmintatusArr;
    }

    public function getSnmpPortOperStatus($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portOperStatus = snmp2_get($ip, $this->readCommunity, self::ifOperStatus . '.' . $portIndex);
                //                error_log('getSnmpPortOperStatus(): $portIndex = '.$portIndex."\n".'$portOperStatus = '.print_r($portOperStatus,true));
                return $this->formatSnmpResponse($portOperStatus);
            }
        }
        return false;
    }

    public function getSnmpAllPortOperStatus($ip, $keyRegEx = '') {
        $portOperStatusArr = self::getSnmpAllPortsQuery($ip, self::ifOperStatus, $keyRegEx);
        foreach ($portOperStatusArr as $key => $operStatusEntry) {
            $operStatusStr = preg_replace('/^(.*)\(.*/', '$1', $operStatusEntry);
            $portOperStatusArr[$key] = $operStatusStr;
        }
        return $portOperStatusArr;
    }

    public function getSnmpPortfastStatus($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $bridgePortIndex = self::getBridgePortIndex($ip, $portNum, $isIdx);
            if ($bridgePortIndex != false) {
                $portfastStatus = snmp2_get($ip, $this->readCommunity, self::ifPortFast . '.' . $bridgePortIndex);
                return $this->formatSnmpResponse($portfastStatus);
            }
        }
        return false;
    }

    public function getSnmpPortfastMode($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $bridgePortIndex = self::getBridgePortIndex($ip, $portNum, $isIdx);
            if ($bridgePortIndex != false) {
                $portfastStatus = snmp2_get($ip, $this->readCommunity, self::ifPortFastMode . '.' . $bridgePortIndex);
                return $this->formatSnmpResponse($portfastStatus);
            }
        }
        return false;
    }

    public function getSnmpBpduGuardStatus($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $bridgePortIndex = self::getBridgePortIndex($ip, $portNum, $isIdx);
            if ($bridgePortIndex != false) {
                $portfastStatus = snmp2_get($ip, $this->readCommunity, self::ifbpduGuard . '.' . $bridgePortIndex);
                return $this->formatSnmpResponse($portfastStatus);
            }
        }
        return false;
    }

    public function getSnmpBpduFilterStatus($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $bridgePortIndex = self::getBridgePortIndex($ip, $portNum, $isIdx);
            if ($bridgePortIndex != false) {
                $portfastStatus = snmp2_get($ip, $this->readCommunity, self::ifbpduFilter . '.' . $bridgePortIndex);
                return $this->formatSnmpResponse($portfastStatus);
            }
        }
        return false;
    }

    public function getSnmpPortLastChange($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portLastChange = snmp2_get($ip, $this->readCommunity, self::ifLastChange . '.' . $portIndex);
                $lastChangeStr = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $portLastChange);
                $sysUptime = self::getSnmpSysUptime($ip);
                $lastPortChangeInt = intval($sysUptime) - intval($lastChangeStr);
                return $lastPortChangeInt;
            }
        }
        return false;
    }

    public function getSnmpAllPortLastChange($ip, $keyRegEx = '') {
        $lastChangeArr = self::getSnmpAllPortsQuery($ip, self::ifLastChange, $keyRegEx);
        $sysUptime = self::getSnmpSysUptime($ip);
        foreach ($lastChangeArr as $key => $lastChangeEntry) {
            $lastChangeStr = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $lastChangeEntry);
            $lastPortChangeInt = intval($sysUptime) - intval($lastChangeStr);
            $lastChangeArr[$key] = $lastPortChangeInt;
        }
        return $lastChangeArr;
    }

    public function getSnmpPortLastChangeFormatted($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portLastChange = self::getSnmpPortLastChange($ip, $portNum, $isIdx);
            if ($portLastChange != false) {
                $portLastChangeTimeString = self::getTimeString($portLastChange);
                return $portLastChangeTimeString;
            }
        }
        return false;
    }

    public function getSnmpAllPortLastChangeFormatted($ip, $keyRegEx = '') {
        $lastChangeArr = self::getSnmpAllPortLastChange($ip, $keyRegEx);
        foreach ($lastChangeArr as $key => $lastChangeEntry) {
            $lastChangeArr[$key] = self::getTimeString($lastChangeEntry);
        }
        return $lastChangeArr;
    }

    public function getSnmpPortInDataOct($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portInDataOct = snmp2_get($ip, $this->readCommunity, self::ifInOctets . '.' . $portIndex);
                return $this->formatSnmpResponse($portInDataOct);
            }
        }
        return false;
    }

    public function getSnmpPortOutDataOct($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portOutDataOct = snmp2_get($ip, $this->readCommunity, self::ifOutOctets . '.' . $portIndex);
                return $this->formatSnmpResponse($portOutDataOct);
            }
        }
        return false;
    }

    public function getSnmpPortInDiscards($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portInDiscards = snmp2_get($ip, $this->readCommunity, self::ifInDiscards . '.' . $portIndex);
                return $this->formatSnmpResponse($portInDiscards);
            }
        }
        return false;
    }

    public function getSnmpPortOutDiscards($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            //            $portOutDiscards = snmp2_get($ip, $this->readCommunity, 'ifOutDiscards.'. $portIndex);
            $portOutDiscards = snmp2_get($ip, $this->readCommunity, self::ifOutDiscards . '.' . $portIndex);
            return $this->formatSnmpResponse($portOutDiscards);
        }
        return false;
    }

    public function getSnmpPortInErrors($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portInErrors = snmp2_get($ip, $this->readCommunity, self::ifInErrors . '.' . $portIndex);
                return $this->formatSnmpResponse($portInErrors);
            }
        }
        return false;
    }

    public function getSnmpPortOutErrors($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portOutErrors = snmp2_get($ip, $this->readCommunity, self::ifOutErrors . '.' . $portIndex);
                return $this->formatSnmpResponse($portOutErrors);
            }
        }
        return false;
    }

    public function getSnmpPortInBroadcastPkts($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portInBroadcastPkts = snmp2_get($ip, $this->readCommunity, self::ifInBroadcastPkts . '.' . $portIndex);
                return $this->formatSnmpResponse($portInBroadcastPkts);
            }
        }
        return false;
    }

    public function getSnmpPortOutBroadcastPkts($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portOutBroadcastPkts = snmp2_get($ip, $this->readCommunity, self::ifOutBroadcastPkts . '.' . $portIndex);
                return $this->formatSnmpResponse($portOutBroadcastPkts);
            }
        }
        return false;
    }

    public function getSnmpPortStats($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $inOct = self::getSnmpPortInDataOct($ip, $portIndex, true);
                $outOct = self::getSnmpPortOutDataOct($ip, $portIndex, true);
                $inErrors = self::getSnmpPortInErrors($ip, $portIndex, true);
                $outErros = self::getSnmpPortOutErrors($ip, $portIndex, true);
                $inDiscards = self::getSnmpPortInDiscards($ip, $portIndex, true);
                $outDiscards = self::getSnmpPortOutDiscards($ip, $portIndex, true);
                $portStats = array('inOctets' => $inOct,
                                   'outOctets' => $outOct,
                                   'inErrors' => $inErrors,
                                   'outErrors' => $outErros,
                                   'inDiscards' => $inDiscards,
                                   'outDiscards' => $outDiscards);
                return $portStats;
            }
        }
        return false;
    }

    public function getSnmpPortMode($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portMode = snmp2_get($ip, $this->readCommunity, self::ifModeOper . '.' . $portIndex);
                return $this->formatSnmpResponse($portMode);
            }
        }
        return false;
    }

    public function getSnmpSwitchportMode($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $switchportMode = snmp2_get($ip, $this->readCommunity, self::ifswitchportMode . '.' . $portIndex);
                return $this->formatSnmpResponse($switchportMode);
            }
        }
        return false;
    }

    public function getSnmpPortVlanAssignment($ip, $port, $isIdx = false) {
        $portVlans = array();
        if (isset($ip) && $ip != NULL && isset($port) && $port != NULL) {
            $portMode = self::getSnmpSwitchportMode($ip, $port, $isIdx);
            if ($portMode == self::trunk) {
                $portIndex = self::getPortIndex($ip, $port, $isIdx);
                $vlanList = snmp2_get($ip, $this->readCommunity, self::vlanTrunkPortVlansEnabled . '.' . $portIndex);

                if ($vlanList != false) {
                    $vlanList = str_replace('Hex-STRING:', '', $vlanList);
                }

                $vlanListTrimmed = preg_replace('/\s+/', '', $vlanList);
                $vlanListTrimmed = trim($vlanListTrimmed);
                $vlanListArr = str_split($vlanListTrimmed);
                $vlanListBinary = '';
                foreach ($vlanListArr as $octet) {
                    $hex2Bin = base_convert($octet, 16, 2);
                    if ($hex2Bin == 0) {
                        $vlanListBinary .= '0000';
                    } else {
                        if (strlen($hex2Bin) < 4) {
                            $vlanListBinary .= '0' . $hex2Bin;
                        } else {
                            $vlanListBinary .= $hex2Bin;
                        }
                    }
                }

                $portVlans = self::strpos_all($vlanListBinary, '1');
            } else {
                $portVlans[] = self::getAccessPortVlan($ip, $port, $isIdx);
            }
        }
        return $portVlans;
    }

    private function getAccessPortVlan($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                //                error_log("getAccessPortVlan(): snmp2_get($ip, $this->readCommunity, self::vmVlan . '.' . $portIndex)");
                $portVlanID = snmp2_get($ip, $this->readCommunity, self::vmVlan . '.' . $portIndex);
                //                error_log('getAccessPortVlan(): $portVlanID = '.$portVlanID);
                return $this->formatSnmpResponse($portVlanID);
            }
        }
        return false;
    }

    /**
     * 
     * @param type $ip
     * @param type $portNum
     * @return 0 based position of $portNum on the switch (e.g. port 1 = 0, port 2 = 1, port 3/5 = 99 and so on)
     */
    public function getPortPositionByPortNumber($ip, $portNum) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $rawPortIndexList = self::getSnmpPortIndexList($ip);
            $portIndexList = self::filterValidUserPorts($ip, $rawPortIndexList);
            $portDescArray = array_keys($portIndexList);
            $portPositionArray = array_flip($portDescArray);
            $portNamePrefix = self::getPortNamePrefix($ip);
            //            error_log('getPortPositionByPortNumber(): $rawPortIndexList = '.print_r($rawPortIndexList,true));
            //            error_log('getPortPositionByPortNumber(): $portIndexList = '.print_r($portIndexList,true));
            //            error_log('getPortPositionByPortNumber(): $portDescArray = '.print_r($portDescArray,true));
            //            error_log('getPortPositionByPortNumber(): $portPositionArray = '.print_r($portPositionArray,true));
            //            error_log('getPortPositionByPortNumber(): $portNamePrefix = '.$portNamePrefix);
            return $portPositionArray[$portNamePrefix . $portNum];
        }
        return false;
    }

    /**
     * 
     * @param type $portIndexList
     * @return array port index list without the non-user ports
     */
    public function filterValidUserPorts($ip, $portIndexList) {
        $switchModel = self::getSnmpModelNumber($ip);
        if ($switchModel != false) {
            if (strstr($switchModel, 'WS-C6509')) {
                foreach ($portIndexList as $key => $value) {
                    if (strpos($key, 'GigabitEthernet') !== 0 || strpos($key, 'GigabitEthernet5') === 0 || strpos($key, 'GigabitEthernet6') === 0) {
                        unset($portIndexList[$key]);
                    }
                }
            } else if (strstr($switchModel, 'WS-C2960G') || strstr($switchModel, 'WS-C3750G') || strstr($switchModel, 'WS-C3560G')) {
                foreach ($portIndexList as $key => $value) {
                    if (strpos($key, 'GigabitEthernet') !== 0) {
                        unset($portIndexList[$key]);
                    }
                }
            } else if (strstr($switchModel, 'WS-C2960-') || strstr($switchModel, 'WS-C3560-') || strstr($switchModel, 'WS-C3750-')) {
                foreach ($portIndexList as $key => $value) {
                    if (strpos($key, 'FastEthernet') !== 0) {
                        unset($portIndexList[$key]);
                    }
                }
            }
        }
        return $portIndexList;
    }

    public function getPortNamePrefix($ip, $switchModel = false) {
        if (isset($ip) && $ip != NULL) {
            if ($switchModel == '') {
                $switchModel = self::getSnmpModelNumber($ip);
            }

            if ($switchModel != false) {
                $portNamePrefix = '';
                if (strstr($switchModel, 'WS-C2950')) {
                    $portNamePrefix = 'FastEthernet0/';
                } elseif (strstr($switchModel, 'WS-C2960G')) {
                    $portNamePrefix = 'GigabitEthernet0/';
                } elseif (strstr($switchModel, 'WS-C2960-')) {
                    //                    if ($portNum > 100) {
                    //                        $portNamePrefix = 'GigabitEthernet0/';
                    //                        $portNum = $portNum - 100;
                    //                    } else {
                    $portNamePrefix = 'FastEthernet0/';
                    //                    }
                } elseif (strstr($switchModel, 'WS-C3750G')) {
                    $portNamePrefix = 'GigabitEthernet1/0/';
                } elseif (strstr($switchModel, 'WS-C3560G')) {
                    $portNamePrefix = 'GigabitEthernet0/';
                } elseif (strstr($switchModel, 'WS-C6509')) {
                    $portNamePrefix = 'GigabitEthernet';
                }
                return $portNamePrefix;
            }
        }
        return false;
    }

    public function getSnmpTrunkPortNativeVlanAssignment($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $portVlanID = snmp2_get($ip, $this->readCommunity, self::vlanTrunkPortNativeVlan . '.' . $portIndex);
                return $this->formatSnmpResponse($portVlanID);
            }
        }
        return false;
    }

    public function getSnmpTrunkPortEncapsulation($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $trunkPortEncapsulation = snmp2_get($ip, $this->readCommunity, self::vlanTrunkPortEncapsulationType . '.' . $portIndex);
                return $this->formatSnmpResponse($trunkPortEncapsulation);
            }
        }
        return false;
    }

    public function setSnmpPortVlanAssignment($ip, $portNum, $vlanID, $isIdx = false) {
        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2

        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL && isset($vlanID) && $vlanID != NULL) {
            $portIndex = '';
            if (isset($isIdx) && $isIdx == false) {
                //                $portIndex = getSnmpPortIndex($ip, $portNum);
                $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            } else {
                $portIndex = $portNum;
            }

            if (snmp2_set($ip, $this->writeCommunity, self::vmVlan . '.' . $portIndex, 'i', $vlanID, '1000000', '5')) {
                return true;
            }
        }
        return false;
    }

    public function getSnmpCdpCacheDeviceId($ip) {
        if (isset($ip) && $ip != NULL) {
            $cdpCacheDeviceIdSnmpResponse = snmp2_real_walk($ip, $this->readCommunity, self::cdpCacheDeviceId);
            //            error_log('getSnmpCdpCacheDeviceId(): '.print_r($cdpCacheDeviceIdSnmpResponse,true));
            return $this->formatSnmpResponse($cdpCacheDeviceIdSnmpResponse, 2);
        }
        return false;
    }

    public function getSnmpCdpCachePlatform($ip) {
        if (isset($ip) && $ip != NULL) {
            $cdpCachePlatformSnmpResponse = snmp2_real_walk($ip, $this->readCommunity, self::cdpCachePlatform);
            return $this->formatSnmpResponse($cdpCachePlatformSnmpResponse, 2);
        }
        return false;
    }

    public function snmpPortOn($ip, $portNum, $isIdx = false) {
        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = '';
            if (isset($isIdx) && $isIdx == false) {
                $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            } else {
                $portIndex = $portNum;
            }
            //            error_log('$portNum = '.$portNum.', $portIndex = '.$portIndex.', $isIdx = '.$isIdx);
            if ($portIndex != false) {
                //                error_log('Calling: snmp2_set('.$ip.', '.$this->writeCommunity.', '.self::ifAdminStatus.'.'.$portIndex.', \'i\', 1, \'1000000\', \'5\')');
                if (snmp2_set($ip, $this->writeCommunity, self::ifAdminStatus . '.' . $portIndex, 'i', 1, '1000000', '5')) {
                    return true;
                }
            }
        }
        return false;
    }

    public function snmpPortOff($ip, $portNum, $isIdx = false) {
        //	1.3.6.1.2.1.2.2.1.7
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = '';
            if (isset($isIdx) && $isIdx == false) {
                $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            } else {
                $portIndex = $portNum;
            }
            //            error_log('$portNum = '.$portNum.', $portIndex = '.$portIndex.', $isIdx = '.$isIdx);
            if ($portIndex != false) {
                //                error_log('Calling: snmp2_set('.$ip.', '.$this->writeCommunity.', '.self::ifAdminStatus.'.'.$portIndex.', \'i\', 2, \'1000000\', \'5\')');
                if (snmp2_set($ip, $this->writeCommunity, self::ifAdminStatus . '.' . $portIndex, 'i', 2, '1000000', '5')) {
                    return true;
                }
            }
        }
        return false;
    }

    public function snmpPortRecycle($ip, $portNum, $isIdx = false) {
        if (isset($ip) && $ip != NULL && isset($portNum) && $portNum != NULL) {
            $portIndex = self::getPortIndex($ip, $portNum, $isIdx);
            if ($portIndex != false) {
                $snmpStatus = self::snmpPortOff($ip, $portIndex, true);
                sleep(3);
                $snmpStatus = self::snmpPortOn($ip, $portIndex, true);
                sleep(3);
                return $snmpStatus;
            }
        }
        return false;
    }

    private function getPortIndex($ip, $portNum, $isIdx = false) {
        if (isset($isIdx)) {
            if ($isIdx) {
                if ($portNum > 100) {
                    $switchModel = self::getSnmpModelNumber($ip);
                    //                error_log('inside getSnmpPortIndex(): $switchModel = '.$switchModel);
                    if ($switchModel != false) {
                        if (strstr($switchModel, 'WS-C2960-24')) {
                            $portNum = $portNum - 100 + 24;
                        } elseif (strstr($switchModel, 'WS-C2960-48')) {
                            $portNum = $portNum - 100 + 48;
                        }
                    }
                }
                return $portNum;
            }
            //            error_log('Calling getSnmpPortIndex('.$ip.', '.$portNum.')');
            $portIndex = self::getSnmpPortIndex($ip, $portNum);
            return $portIndex;
        }
        return false;
    }

    public function getSnmpPortIndex($ip, $portNum) {
        //      1.3.6.1.4.1.9.9.68.1.2.2.1.2
        $portIndex = null;

        if (isset($ip) && $ip != NULL) {
            $switchModel = self::getSnmpModelNumber($ip);
            if ($switchModel != false) {
                $portNamePrefix = self::getPortNamePrefix($ip, $switchModel);
                if ($portNamePrefix != false) {
                    $portIndexList = self::getSnmpPortIndexList($ip);
                    if (is_numeric($portNum)) {
                        $portIndex = $portIndexList[$portNamePrefix . $portNum];
                    } else {
                        if (strstr($switchModel, 'WS-C6509')) {
                            $portIndex = $portIndexList[$portNamePrefix . $portNum];
                        } else {
                            $portIndex = $portIndexList[$portNum];
                        }
                    }
                    return $portIndex;
                }
            }
        }

        return false;
    }

    public function getSnmpPortIndexList($ip) {
        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2

        if (isset($ip) && $ip != NULL) {

            $getPortIndexList = false;
            if (!isset($this->ipAddress) || $ip != $this->ipAddress) {
                $this->ipAddress = $ip;
                $getPortIndexList = true;
            } else {
                if (isset($this->portIndexList)) {
                    return $this->portIndexList;
                } else {
                    $getPortIndexList = true;
                }
            }

            if ($getPortIndexList) {
                $this->portIndexList = snmp2_real_walk($this->ipAddress, $this->readCommunity, self::portIndexQueryStr);
                if ($this->portIndexList != false) {
                    $this->portIndexList = str_replace('STRING:', '', $this->portIndexList);
                    $this->portIndexList = str_replace('"', '', $this->portIndexList);
                    foreach ($this->portIndexList as $key => $ifName) {
                        $this->portIndexList[$key] = trim($ifName);
                    }
                    $this->portIndexList = array_flip($this->portIndexList);
                    $this->portIndexList = preg_replace('/^.+\./', '', $this->portIndexList);
                    //                error_log('in getSnmpPortIndexList(): '. print_r($this->portIndexList, true));
                    return $this->portIndexList;
                }
            }
        }
        return false;
    }

    public function getBridgePortIndex($ip, $port, $isIdx = false) {
        $portVlans = self::getSnmpPortVlanAssignment($ip, $port, $isIdx);
        if (count($portVlans) > 0) {
            $ifIndex = self::getPortIndex($ip, $port, $isIdx);
            $firstVlan = $portVlans[0];
            $this->bridgePortIndexList = snmp2_real_walk($ip, $this->readCommunity . '@' . $firstVlan, self::dot1dBasePortIfIndex);

            if ($this->bridgePortIndexList != false) {
                $this->bridgePortIndexList = str_replace('INTEGER:', '', $this->bridgePortIndexList);
                foreach ($this->bridgePortIndexList as $key => $value) {
                    $this->bridgePortIndexList[$key] = trim($value);
                }
                $this->bridgePortIndexList = array_flip($this->bridgePortIndexList);
                $this->bridgePortIndexList = preg_replace('/^.+\./', '', $this->bridgePortIndexList);
                //                error_log('in getSnmpPortIndexList(): '. print_r($this->portIndexList, true));
                return $this->bridgePortIndexList[$ifIndex];
            }
        }
        return false;
    }

    public function ticksToTimeArray($inputTimeTicks) {

        $inputTimeTicksInt = intval($inputTimeTicks);
        $inputTimeSecs = $inputTimeTicksInt / 100;

        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputTimeSecs / $secondsInADay);

        // extract hours
        $hourSeconds = $inputTimeSecs % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // return the final array
        $obj = array(
            'd' => (int) $days,
            'h' => (int) $hours,
            'm' => (int) $minutes,
            's' => (int) $seconds,
        );
        return $obj;
    }

    public function getTimeString($inputTimeTicks) {
        if (isset($inputTimeTicks) && $inputTimeTicks != NULL) {
            $timeArray = self::ticksToTimeArray($inputTimeTicks);
            if (strlen($timeArray['h']) == 1) {
                $timeArray['h'] = '0' . $timeArray['h'];
            }
            if (strlen($timeArray['m']) == 1) {
                $timeArray['m'] = '0' . $timeArray['m'];
            }
            if (strlen($timeArray['s']) == 1) {
                $timeArray['s'] = '0' . $timeArray['s'];
            }

            return $timeArray['d'] . " days, " . $timeArray['h'] . ":" . $timeArray['m'] . ":" . $timeArray['s'];
        }
        return '';
    }

    //     public function getTimeString($inputTimeTicks) {
    //        if(isset($inputTimeTicks) && $inputTimeTicks != NULL) {
    //            $inputTimeSecs = $inputTimeTicks/100;
    //            $newTime = mktime(0,0,$inputTimeSecs);
    //            
    //            $days = date()
    //            $timeArray = self::ticksToTimeArray($inputTimeTicks);
    //            if(strlen($timeArray['h']) == 1){ $timeArray['h'] = '0'.$timeArray['h']; }
    //            if(strlen($timeArray['m']) == 1){ $timeArray['m'] = '0'.$timeArray['m']; }
    //            if(strlen($timeArray['s']) == 1){ $timeArray['s'] = '0'.$timeArray['s']; }
    //    
    //            return $timeArray['d'] . " days, " . $timeArray['h'] . ":" . $timeArray['m'] . ":" . $timeArray['s'];
    //        }
    //        return '';
    //    }

    public function formatSnmpResponse($snmpResponse, $numOfOidSectionsToKeep = 1) {
        if (is_array($snmpResponse)) {
            $keyPattern = '';
            $keyReplace = '';
            --$numOfOidSectionsToKeep;
            if ($numOfOidSectionsToKeep > 0) {
                $keyPattern = '/.*\.([\w]+\.){' . $numOfOidSectionsToKeep . '}([\w]+)$/';
                $keyReplace = '$1$2';
                //                error_log('formatSnmpResponse(): $keyPattern = ' . $keyPattern);
            } else {
                $keyPattern = '/.*\./';
                $keyReplace = '';
                //                error_log('formatSnmpResponse(): $keyPattern = ' . $keyPattern);
            }
            foreach ($snmpResponse as $key => $val) {
                $newKey = preg_replace($keyPattern, $keyReplace, $key);
                //                error_log('formatSnmpResponse(): $newKey = ' . $newKey);
                //                $newKey = preg_replace($keyPattern, '', $key);
                $newVal = preg_replace('/.+:/', '', $val);
                $newVal = trim(preg_replace('/"/', '', $newVal));
                unset($snmpResponse[$key]);
                $snmpResponse[$newKey] = $newVal;
            }
            return $snmpResponse;
        } else {
            //            error_log('formatSnmpResponse: $snmpResponse NOT an array.');
            if ($snmpResponse != '') {
                $snmpRespStr = preg_replace('/.+:/', '', $snmpResponse);
                $snmpRespStr = preg_replace('/"/', '', $snmpRespStr);
                return trim($snmpRespStr);
            }
        }
        return false;
    }

    public function getMatchingValueEntries($snmpArray, $keyRegEx = '') {
        if ($keyRegEx != '') {
            foreach ($snmpArray as $key => $val) {
                if (preg_match($keyRegEx, $val) === 0) {
                    unset($snmpArray[$key]);
                }
            }
        }
        return $snmpArray;
    }

    protected function strpos_all($haystack, $needle) {
        $offset = 0;
        $allpos = array();
        while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
            $offset = $pos + 1;
            $allpos[] = $pos;
        }
        return $allpos;
    }

    //    public function formatSnmpResponse($snmpResponse) {
    //        if ($snmpResponse != '') {
    //            $snmpRespStr = preg_replace('/.+:/', '', $snmpResponse);
    //            $snmpRespStr = preg_replace('/"/', '', $snmpRespStr);
    //            return trim($snmpRespStr);
    //        }
    //        return false;
    //    }

    /**
     *    Stores data in the session.
     *    Example: $instance->foo = 'bar';
     *   
     *    @param    name    Name of the datas.
     *    @param    value    Your datas.
     *    @return    void
     * */
    public function __set($name, $value) {
        $this->switch[$name] = $value;
    }

    /**
     *    Gets datas from the session.
     *    Example: echo $instance->foo;
     *   
     *    @param    name    Name of the datas to get.
     *    @return    mixed    Datas stored in session.
     * */
    public function __get($name) {
        if (isset($this->switch[$name])) {
            return $this->switch[$name];
        }
    }

    public function __isset($name) {
        return isset($this->switch[$name]);
    }

    public function __unset($name) {
        unset($this->switch[$name]);
    }

}

?>