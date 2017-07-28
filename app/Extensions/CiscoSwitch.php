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
    private $ip = null;
    private $model = null;
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
    const ifswitchportMode = '1.3.6.1.4.1.9.9.46.1.6.1.1.13'; // 1 or 5: trunk, 2: not trunk (use ifIndex)
    const trunk = 1;
    const trunk_noneg = 5;
    const access = 2;
    const dot1dBasePortIfIndex = '1.3.6.1.2.1.17.1.4.1.2';

    // vtpVlanState - 1.3.6.1.4.1.9.9.46.1.3.1.1.2
    // vlanPortIslVlansAllowed  1.3.6.1.4.1.9.5.1.9.3.1.5


    const vlanTrunkPortVlansEnabled = '1.3.6.1.4.1.9.9.46.1.6.1.1.4';  // starts from 0
    const vlanTrunkPortVlansEnabled2k = '1.3.6.1.4.1.9.9.46.1.6.1.1.17'; // starts from 1024
    const vlanTrunkPortVlansEnabled3k = '1.3.6.1.4.1.9.9.46.1.6.1.1.18'; // starts from 2048
    const vlanTrunkPortVlansEnabled4k = '1.3.6.1.4.1.9.9.46.1.6.1.1.19'; // starts from 3072
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

    public function __construct($props = null)
    {
        if ($props != null)
        {
            if (isset($props['readCommunity']))
            {
                $this->readCommunity = $props['readCommunity'];
                unset($props['readCommunity']);
            }

            if (isset($props['writeCommunity']))
            {
                $this->writeCommunity = $props['writeCommunity'];
                unset($props['writeCommunity']);
            }

            if (isset($props['selected']))
            {
                $this->switch = array();
                foreach ($props as $key => $value)
                {
                    $this->switch[$key] = $value;
                }
            }
        }
    }

    #########################
    #  Misc functions
    #########################

    public function isRegistered($ip = null, $mac = null, $hostName = null)
    {
        $switchInDB = $this->loadFromDB($ip, $mac, $hostName);
        if ($switchInDB)
        {
            return true;
        }

        return false;
    }

    public function register($ipAddressList, $location = null)
    {
        if (isset($ipAddressList) == false && count($ipAddressList) <= 0)
        {
            return false;
        }
        foreach ($ipAddressList as $ip)
        {
            //            $hostName = str_replace('.silverip.net', '', $this->formatSnmpResponse($this->getSnmpSysName($ip)));
            $hostName = $this->formatSnmpResponse($this->getSnmpSysName($ip));
            $mac = $this->getSnmpMacAddress($ip);
            $model = str_replace('"', '', $this->getSnmpModelNumber($ip));
            $netNode = new NetworkNode;
            $netNode->ip_address = $ip;
            $netNode->mac_address = $mac;
            $netNode->host_name = $hostName;
            if ($location != null)
            {
                $netNode->id_address = $location;
            }
            $netNode->id_types = 8;
            $netNode->vendor = 'Cisco';
            $netNode->model = $model;

            $netNode->save();
        }

        return true;
    }

    public function loadFromDB($ip = null, $mac = null, $hostName = null)
    {

        $array = array();
        $dbRecord = null;
        if ($ip)
        {
            $array ['ip_address'] = $ip;
        }
        if ($mac)
        {
            $array ['mac_address'] = $mac;
        }
        if ($hostName)
        {
            $array ['host_name'] = $hostName;
        }

        if (count($array) > 0)
        {
            $netNodeQuery = NetworkNode::where('id_types', config('const.type.switch'));

            foreach ($array as $col => $value)
            {
                $netNodeQuery->where($col, $value);
            }

            $netNode = $netNodeQuery->first();

            if ($netNode == null)
            {
                $this->switch = null;
                $this->selected = false;

                return $this;
            }

            $this->switch['dbModel'] = $netNode;
            $netNodeArray = $netNode->toArray();

            foreach ($netNodeArray as $key => $value)
            {
                $this->switch[$key] = $value;
            }
            $this->switch['selected'] = true;

        }

        return $this;
    }

    public function getSwitchObject()
    {
        return $this->switch;
    }

    #########################
    #  Snmp functions
    #########################

    public function getSnmpSysName($ip)
    {

        // $snmpReults = snmp2_real_walk($ip, $this->readCommunity, 'sysName');
        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::sysName);
        if ( ! isset($response['error']))
        {
            $response['response'] = array_shift($response['response']);
        }

        return $response;
    }

    public function getSnmpModelNumber($ip)
    {

        if ($this->ip == $ip && $this->model != null)
        {
            return $this->model;
        }

        $this->ip = $ip;
        $response = $this->snmp2_real_walk($this->ip, $this->readCommunity, self::entPhysicalDescr);

        if (isset($response['error']))
        {
            return $response;
        }

        $entPhysicalDescrArr = $response['response'];

        //        if (isset($entPhysicalDescrArr) && $entPhysicalDescrArr != false && count($entPhysicalDescrArr) > 0) {
        if (count($entPhysicalDescrArr) > 0)
        {
            foreach ($entPhysicalDescrArr as $oid => $entPhysicalDescr)
            {
                if ($entPhysicalDescr != '""')
                {
                    $response['response'] = $this->formatSnmpResponse($entPhysicalDescr);
                    break;
                }
            }
        }

        return $response;
    }

    public function getSnmpSoftwareVersion($ip)
    {

        // $modelNumber = snmp2_get($ip, $this->readCommunity, 'mib-2.47.1.1.1.1.9.1001');
        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::entPhysicalFirmwareRev);
        if ( ! isset($response['error']))
        {
            $response['response'] = array_shift($response['response']);
        }

        return $response;
    }

    public function getSnmpSysUptime($ip, $formatted = false)
    {

        // $sysUptime = snmp2_get($ip, $this->readCommunity, 'system.sysUpTime.0');
        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::sysUpTime . '.0', true);
        if (isset($response['error']))
        {
            return $response;
        }

        $response['response'] = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $response['response']);

        if ($formatted)
        {
            $response['response'] = $this->getTimeString($response['response']);
        }

        return $response;
    }

    public function getSnmpSysLocation($ip)
    {

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::sysLocation . '.0');
        if ( ! isset($response['error']))
        {
            $response['response'] = $this->formatSnmpResponse(array_shift($response['response']));
        }

        return $response;
    }

    public function getSnmpMacAddress($ip)
    {

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::sysLocation . '.0', true);
        if ( ! isset($response['error']))
        {

            $sysMacAddress = trim(preg_replace('/^.*STRING:\s+/', '', $response['response']));
            $sysMacAddress = preg_replace('/\s+/', ':', $sysMacAddress);
            $sysMacAddress = strtoupper($sysMacAddress);
            $response['response'] = $sysMacAddress;
        }

        return $response;
    }

    public function getSnmpPortLabel($ip, $portNum, $isIdx = false)
    {

        // getSnmpIndexValueByPort($ip, $portNum, $isIdx = false, $oid, $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false)
        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifAlias, false, true, true);
    }

    public function getSnmpAllPortLabel($ip, $keyRegEx = '', $skipLabelPatterns = [])
    {
        $portLabelArray = $this->getSnmpAllPortsQuery($ip, self::ifAlias, $keyRegEx);

        if (count($skipLabelPatterns) > 0 && isset($portLabelArray['error']) == false)
        {
            $portLabelArray['response'] = $this->filterResponseByRegEx($portLabelArray['response'], $skipLabelPatterns);
        }

        return $portLabelArray;
    }

    protected function filterResponseByRegEx($response, $regexArray)
    {
        $patternCollection = collect($response);
        $filtered = $patternCollection->reject(function ($value, $key) use ($regexArray)
        {
            foreach ($regexArray as $regex)
            {
                if (preg_match($regex, $value) !== 0)
                {
                    return $value;
                }
            }
        });

        return $filtered->toArray();
    }

    public function getSnmpPortSpeed($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifSpeed, false, true, true);
    }

    public function getSnmpAllPortSpeed($ip, $keyRegEx = '')
    {
        return $this->getSnmpAllPortsQuery($ip, self::ifSpeed, $keyRegEx);
    }

    public function getSnmpPortAdminStatus($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifAdminStatus, false, true, true);
    }

    public function getSnmpAllPortAdminStatus($ip, $keyRegEx = '')
    {

        $response = $this->getSnmpAllPortsQuery($ip, self::ifAdminStatus, $keyRegEx);

//        return $response;
        return $this->filterResponses($response, '/^(.*)\(.*/', '$1');
    }

    public function getSnmpPortOperStatus($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifOperStatus, false, true, true);
    }

    public function getSnmpAllPortOperStatus($ip, $keyRegEx = '')
    {

        $response = $this->getSnmpAllPortsQuery($ip, self::ifOperStatus, $keyRegEx);

        return $this->filterResponses($response, '/^(.*)\(.*/', '$1');
    }

    public function getSnmpPortfastStatus($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifPortFast, true, true, true);
    }

    public function getSnmpPortfastMode($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifPortFastMode, true, true, true);
    }

    public function getSnmpBpduGuardStatus($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifbpduGuard, true, true, true);
    }

    public function getSnmpBpduFilterStatus($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifbpduFilter, true, true, true);
    }

    public function getSnmpPortLastChange($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        $response = $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifLastChange, false, true, false);

        if (isset($response['error']))
        {
            return $response;
        }

        $portLastChange = $response['response'];
        $lastChangeStr = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $portLastChange);
        $sysUptimeResponse = $this->getSnmpSysUptime($ip);
        if (isset($sysUptimeResponse['error']))
        {
            return $sysUptimeResponse;
        }
        $sysUptime = $sysUptimeResponse['response'];
        $lastPortChangeInt = intval($sysUptime) - intval($lastChangeStr);

        return ['response' => $lastPortChangeInt];
    }

    public function getSnmpAllPortLastChange($ip, $keyRegEx = '')
    {

        $lastChangeResponse = $this->getSnmpAllPortsQuery($ip, self::ifLastChange, $keyRegEx);
        if (isset($lastChangeResponse['error']))
        {
            return $lastChangeResponse;
        }
        $lastChangeArr = $lastChangeResponse['response'];

        $sysUptimeResponse = $this->getSnmpSysUptime($ip);
        if (isset($sysUptimeResponse['error']))
        {
            return $sysUptimeResponse;
        }
        $sysUptime = $sysUptimeResponse['response'];

        foreach ($lastChangeArr as $key => $lastChangeEntry)
        {
            $lastChangeStr = preg_replace('/^.*Timeticks:\s+\((.*)\).*/', '$1', $lastChangeEntry);
            $lastPortChangeInt = intval($sysUptime) - intval($lastChangeStr);
            $lastChangeArr[$key] = $lastPortChangeInt;
        }

        return ['response' => $lastChangeArr];
    }

    public function getSnmpPortLastChangeFormatted($ip, $portNum, $isIdx = false)
    {

        $portLastChangeResponse = $this->getSnmpPortLastChange($ip, $portNum, $isIdx);
        if (isset($portLastChangeResponse['error']))
        {
            return $portLastChangeResponse;
        }
        $portLastChangeTimeString = $this->getTimeString($portLastChangeResponse['response']);

        return ['response' => $portLastChangeTimeString];
    }

    public function getSnmpAllPortLastChangeFormatted($ip, $keyRegEx = '')
    {

        $portLastChangeResponse = $this->getSnmpAllPortLastChange($ip, $keyRegEx);
        if (isset($portLastChangeResponse['error']))
        {
            return $portLastChangeResponse;
        }

        $lastChangeArr = $portLastChangeResponse['response'];
        foreach ($lastChangeArr as $key => $lastChangeEntry)
        {
            $lastChangeArr[$key] = $this->getTimeString($lastChangeEntry);
        }

        return ['response' => $lastChangeArr];
    }

    public function getSnmpPortMode($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifswitchportMode, false, true, true);
    }

    public function getSnmpSwitchportMode($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifswitchportMode, false, true, true);
    }

    public function getSnmpPortVlanAssignment($ip, $port, $isIdx = false)
    {

        $switchportModeResponse = $this->getSnmpSwitchportMode($ip, $port, $isIdx);
        if (isset($switchportModeResponse['error']))
        {
            return $switchportModeResponse;
        }

        $portMode = $switchportModeResponse['response'];
        $response = ['response' => array()];
        if ($portMode != self::trunk && $portMode != self::trunk_noneg)
        {

            return $this->getAccessPortVlan($ip, $port, $isIdx);
        }

        $vlanHexStringResponse = $this->getSnmpIndexValueByPort($ip, $port, $isIdx, self::vlanTrunkPortVlansEnabled, false, true, true);
        $vlanHexStringResponse2k = $this->getSnmpIndexValueByPort($ip, $port, $isIdx, self::vlanTrunkPortVlansEnabled2k, false, true, true);
        $vlanHexStringResponse3k = $this->getSnmpIndexValueByPort($ip, $port, $isIdx, self::vlanTrunkPortVlansEnabled3k, false, true, true);
        $vlanHexStringResponse4k = $this->getSnmpIndexValueByPort($ip, $port, $isIdx, self::vlanTrunkPortVlansEnabled4k, false, true, true);

        if (isset($vlanHexStringResponse['error']))
        {
            return $vlanHexStringResponse;
        }

        $vlanHexString = $vlanHexStringResponse['response'];
        $vlanHexString2k = $vlanHexStringResponse2k['response'];
        $vlanHexString3k = $vlanHexStringResponse3k['response'];
        $vlanHexString4k = $vlanHexStringResponse4k['response'];

        $responseArray = array();
        if ($vlanHexString != '')
        {
            $vlanListBinary = $this->hexString2Bin($vlanHexString);
            $responseArray = $this->strpos_all($vlanListBinary, '1');
        }

        if ($vlanHexString2k != '')
        {
            $vlanListBinary2k = $this->hexString2Bin($vlanHexString2k);
            $responseArray = array_merge($responseArray, $this->strpos_all($vlanListBinary2k, '1', 1024));
        }

        if ($vlanHexString3k != '')
        {
            $vlanListBinary3k = $this->hexString2Bin($vlanHexString3k);
            $responseArray = array_merge($responseArray, $this->strpos_all($vlanListBinary3k, '1', 2048));
        }

        if ($vlanHexString4k != '')
        {
            $vlanListBinary4k = $this->hexString2Bin($vlanHexString4k);
            $responseArray = array_merge($responseArray, $this->strpos_all($vlanListBinary4k, '1', 3072));
        }

        return ['response' => $responseArray];
    }

    protected function hexString2Bin($vlanHexString)
    {

        $vlanListTrimmed = preg_replace('/\s+/', '', $vlanHexString);
        $vlanListTrimmed = trim($vlanListTrimmed);
        $vlanListArr = str_split($vlanListTrimmed);
        $vlanListBinary = '';
        foreach ($vlanListArr as $octet)
        {
            $hex2Bin = base_convert($octet, 16, 2);
            if ($hex2Bin == 0)
            {
                $vlanListBinary .= '0000';
            } else
            {
                if (strlen($hex2Bin) < 4)
                {
                    $vlanListBinary .= str_pad($hex2Bin, 4, '0', STR_PAD_LEFT);
                } else
                {
                    $vlanListBinary .= $hex2Bin;
                }
            }
        }

        return $vlanListBinary;
    }

    protected function getAccessPortVlan($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        $response = $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::vmVlan, false, true, true);
        if (isset($response['error']))
        {
            return $response;
        }

        return ['response' => array($response['response'])];
    }

    /**
     *
     * @param string $ip
     * @param string $portNum
     * @return 0 based position of $portNum on the switch (e.g. port 1 = 0, port 2 = 1, port 3/5 = 99 and so on)
     *          This is used by the private vlan calculator function
     */
    public function getPortPositionByPortNumber($ip, $portNum)
    {

        $response = $this->getSnmpPortIndexList($ip);
        if (isset($response['error']))
        {
            return false;
        }
        $portIndexList = $this->filterValidUserPorts($ip, $response['response']);
        $portDescArray = array_keys($portIndexList);
        $portPositionArray = array_flip($portDescArray);
        $portNamePrefix = $this->getPortNamePrefix($ip);

        return $portPositionArray[$portNamePrefix . $portNum];
    }

    /**
     *
     * @param type $portIndexList
     * @return array port index list without the non-user ports
     */
    public function filterValidUserPorts($ip, $portIndexList)
    {

        $response = $this->getSnmpModelNumber($ip);
        if (isset($response['error']))
        {
            return $portIndexList;
        }
        $switchModel = $response['response'];
        if ($switchModel != false)
        {
            if (strstr($switchModel, 'WS-C6509'))
            {
                foreach ($portIndexList as $key => $value)
                {
                    if (strpos($key, 'GigabitEthernet') !== 0 || strpos($key, 'GigabitEthernet5') === 0 || strpos($key, 'GigabitEthernet6') === 0)
                    {
                        unset($portIndexList[$key]);
                    }
                }
            } else if (strstr($switchModel, 'WS-C2960G') || strstr($switchModel, 'WS-C3750G') || strstr($switchModel, 'WS-C3560G'))
            {
                foreach ($portIndexList as $key => $value)
                {
                    if (strpos($key, 'GigabitEthernet') !== 0)
                    {
                        unset($portIndexList[$key]);
                    }
                }
            } else if (strstr($switchModel, 'WS-C2960-') || strstr($switchModel, 'WS-C3560-') || strstr($switchModel, 'WS-C3750-'))
            {
                foreach ($portIndexList as $key => $value)
                {
                    if (strpos($key, 'FastEthernet') !== 0)
                    {
                        unset($portIndexList[$key]);
                    }
                }
            }
        }

        return $portIndexList;
    }

    protected function getPortNamePrefix($ip, $switchModel = false)
    {
        if (isset($ip) && $ip != null)
        {
            if ($switchModel == false || $switchModel == '')
            {
                $switchModelResponse = $this->getSnmpModelNumber($ip);
                if (isset($switchModelResponse['error']))
                {
                    return false;
                }
                $switchModel = $switchModelResponse['response'];
            }

            if ($switchModel != false)
            {
                $portNamePrefix = '';
                if (strstr($switchModel, 'WS-C2950'))
                {
                    $portNamePrefix = 'FastEthernet0/';
                } elseif (strstr($switchModel, 'WS-C2960G'))
                {
                    $portNamePrefix = 'GigabitEthernet0/';
                } elseif (strstr($switchModel, 'WS-C2960-'))
                {
                    //                    if ($portNum > 100) {
                    //                        $portNamePrefix = 'GigabitEthernet0/';
                    //                        $portNum = $portNum - 100;
                    //                    } else {
                    $portNamePrefix = 'FastEthernet0/';
                    //                    }
                } elseif (strstr($switchModel, 'WS-C3750G'))
                {
                    $portNamePrefix = 'GigabitEthernet1/0/';
                } elseif (strstr($switchModel, 'WS-C3560G'))
                {
                    $portNamePrefix = 'GigabitEthernet0/';
                } elseif (strstr($switchModel, 'WS-C6509'))
                {
                    $portNamePrefix = 'GigabitEthernet';
                }

                return $portNamePrefix;
            }
        }

        return false;
    }

    public function getSnmpTrunkPortNativeVlanAssignment($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::vlanTrunkPortNativeVlan, false, true, true);
    }

    public function getSnmpTrunkPortEncapsulation($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::vlanTrunkPortEncapsulationType, false, true, true);
    }

    public function getSnmpCdpCacheDeviceId($ip)
    {

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::cdpCacheDeviceId);
        if (isset($response['error']))
        {
            return $response;
        }

        $cdpCacheDeviceIdSnmpResponse = $response['response'];

        return $this->formatSnmpResponse($cdpCacheDeviceIdSnmpResponse, 2);
    }

    public function getSnmpCdpCachePlatform($ip)
    {

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::cdpCachePlatform);
        if (isset($response['error']))
        {
            return $response;
        }

        $cdpCachePlatformSnmpResponse = $response['response'];

        return $this->formatSnmpResponse($cdpCachePlatformSnmpResponse, 2);
    }

    public function getSnmpPortIndex($ip, $portNum)
    {

        // 1.3.6.1.4.1.9.9.68.1.2.2.1.2
        $response = $this->getSnmpModelNumber($ip);
        if (isset($response['error']))
        {
            return $response;
        }
        $switchModel = $response['response'];

        $portNamePrefix = $this->getPortNamePrefix($ip, $response['response']);
        if ($portNamePrefix == false)
        {
            return ['error' => 'port name prefix not found. Check getPortNamePrefix()'];
        }

        $portIndexListResponse = $this->getSnmpPortIndexList($ip);
        if (isset($portIndexListRsponse['error']))
        {
            return $portIndexListResponse;
        }

        $portIndexList = $portIndexListResponse['response'];
        $portIndex = null;
        if (is_numeric($portNum))
        {
            $portIndex = $portIndexList[$portNamePrefix . $portNum];
        } else
        {
            if (strstr($switchModel, 'WS-C6509'))
            {
                $portIndex = $portIndexList[$portNamePrefix . $portNum];
            } else
            {
                $portIndex = $portIndexList[$portNum];
            }
        }

        return ['response' => $portIndex];
    }

    public function getSnmpPortIndexList($ip)
    {

        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2
        if (isset($this->ipAddress) && $ip == $this->ipAddress && isset($this->portIndexList))
        {
            return ['response' => $this->portIndexList];
        }

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::portIndexQueryStr);
        if (isset($response['error']))
        {
            return $response;
        }
        $this->ipAddress = $ip;
        $this->portIndexList = str_replace('STRING:', '', $response['response']);
        $this->portIndexList = str_replace('"', '', $this->portIndexList);
        foreach ($this->portIndexList as $key => $ifName)
        {
            $this->portIndexList[$key] = trim($ifName);
        }
        $this->portIndexList = array_flip($this->portIndexList);
        $this->portIndexList = preg_replace('/^.+\./', '', $this->portIndexList);
        $response['response'] = $this->portIndexList;

        return $response;
    }

    protected function getPortIndex($ip, $portNum, $isIdx = false)
    {

        if ($isIdx)
        {
            if ($portNum > 100)
            {
                $response = $this->getSnmpModelNumber($ip);
                if (isset($response['error']))
                {
                    return $response;
                }
                $switchModel = $response['response'];
                if (strstr($switchModel, 'WS-C2960-24'))
                {
                    $portNum = $portNum - 100 + 24;
                } elseif (strstr($switchModel, 'WS-C2960-48'))
                {
                    $portNum = $portNum - 100 + 48;
                }
            }

            return ['response' => $portNum];
        }

        return $this->getSnmpPortIndex($ip, $portNum);
    }

    public function getBridgePortIndex($ip, $port, $isIdx = false)
    {

        $portVlanResponse = $this->getSnmpPortVlanAssignment($ip, $port, $isIdx);
        if (isset($portVlanResponse['error']))
        {
            return $portVlanResponse;
        }
        $portVlans = $portVlanResponse['response'];

        if (count($portVlans) > 0)
        {

            $ifIndexResponse = $this->getPortIndex($ip, $port, $isIdx);
            if (isset($ifIndexResponse['error']))
            {
                return $ifIndexResponse;
            }
            $ifIndex = $ifIndexResponse['response'];
            $firstVlan = $portVlans[0];
            $bridgePortIndexResponse = $this->snmp2_real_walk($ip, $this->readCommunity . '@' . $firstVlan, self::dot1dBasePortIfIndex);
            if (isset($bridgePortIndexResponse['error']))
            {
                return $bridgePortIndexResponse;
            }

            $this->bridgePortIndexList = str_replace('INTEGER:', '', $bridgePortIndexResponse['response']);
            foreach ($this->bridgePortIndexList as $key => $value)
            {
                $this->bridgePortIndexList[$key] = trim($value);
            }
            $this->bridgePortIndexList = array_flip($this->bridgePortIndexList);
            $this->bridgePortIndexList = preg_replace('/^.+\./', '', $this->bridgePortIndexList);

            return ['response' => $this->bridgePortIndexList[$ifIndex]];
        }

        return ['error' => 'no vlans found on port'];
    }

    public function setSnmpSysLocation($ip, $sysLocation)
    {

        return $this->setSnmpIndexValue($ip, self::sysLocation . '.0', 's', '"' . $sysLocation . '"');
    }

    public function setSnmpPortLabel($ip, $portNum, $label, $isIdx = false)
    {

        return $this->setSnmpIndexValueByPort($ip, $portNum, $isIdx, false, self::ifAlias, 's', $label);
    }

    public function setSnmpPortVlanAssignment($ip, $portNum, $vlanID, $isIdx = false)
    {

        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2
        return $this->setSnmpIndexValueByPort($ip, $portNum, $isIdx, false, self::vmVlan, 'i', $vlanID);
    }

    public function snmpPortOn($ip, $portNum, $isIdx = false)
    {

        //	1.3.6.1.4.1.9.9.68.1.2.2.1.2
        return $this->setSnmpIndexValueByPort($ip, $portNum, $isIdx, false, self::ifAdminStatus, 'i', 1);
    }

    public function snmpPortOff($ip, $portNum, $isIdx = false)
    {

        //	1.3.6.1.2.1.2.2.1.7
        return $this->setSnmpIndexValueByPort($ip, $portNum, $isIdx, false, self::ifAdminStatus, 'i', 2);
    }

    public function snmpPortRecycle($ip, $portNum, $isIdx = false)
    {

        $snmpStatus = $this->snmpPortOff($ip, $portNum, $isIdx);
        sleep(3);
        $snmpStatus = $this->snmpPortOn($ip, $portNum, $isIdx);

        return $snmpStatus;
    }

    public function getSnmpPortInDataOct($ip, $portNum, $isIdx = false)
    {

        // $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false
        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifInOctets, false, true, true);
    }

    public function getSnmpPortOutDataOct($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifOutOctets, false, true, true);
    }

    public function getSnmpPortInDiscards($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifInDiscards, false, true, true);
    }

    public function getSnmpPortOutDiscards($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifOutDiscards, false, true, true);
    }

    public function getSnmpPortInErrors($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifInErrors, false, true, true);
    }

    public function getSnmpPortOutErrors($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifOutErrors, false, true, true);
    }

    public function getSnmpPortInBroadcastPkts($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifInBroadcastPkts, false, true, true);
    }

    public function getSnmpPortOutBroadcastPkts($ip, $portNum, $isIdx = false)
    {

        return $this->getSnmpIndexValueByPort($ip, $portNum, $isIdx, self::ifOutBroadcastPkts, false, true, true);
    }

    public function getSnmpPortStats($ip, $portNum, $isIdx = false)
    {

        $portIndexResponse = $this->getPortIndex($ip, $portNum, $isIdx);
        if (isset($portIndexResponse['error']))
        {
            return $portIndexResponse;
        }
        $portIndex = $portIndexResponse['response'];

        $inOct = $this->getSnmpPortInDataOct($ip, $portIndex, true);
        $outOct = $this->getSnmpPortOutDataOct($ip, $portIndex, true);
        $inErrors = $this->getSnmpPortInErrors($ip, $portIndex, true);
        $outErros = $this->getSnmpPortOutErrors($ip, $portIndex, true);
        $inDiscards = $this->getSnmpPortInDiscards($ip, $portIndex, true);
        $outDiscards = $this->getSnmpPortOutDiscards($ip, $portIndex, true);
        $portStats = array('inOctets'    => $inOct['response'],
                           'outOctets'   => $outOct['response'],
                           'inErrors'    => $inErrors['response'],
                           'outErrors'   => $outErros['response'],
                           'inDiscards'  => $inDiscards['response'],
                           'outDiscards' => $outDiscards['response']);

        return ['response' => $portStats];
    }


    #########################
    #  Supporting functions
    #########################

    public function getSnmpAllPortDesc($ip, $keyRegEx = '')
    {

        $response = $this->snmp2_real_walk($ip, $this->readCommunity, self::portIndexQueryStr);
        if ( ! isset($response['error']))
        {
            $response['response'] = $this->formatSnmpResponse($response['response']);
            $response['response'] = $this->getMatchingValueEntries($response['response'], $keyRegEx);
        }

        return $response;
    }

    /**
     * This function will SNMP walk all ports and snmp format the results based on the specified regex filter
     * @param  string $ip IP address of the switch
     * @param  string $oid SNMP OID to run the SNMP walk on
     * @param  string [$keyRegEx = ''] This will be used to fomat the snmp responses from the walk
     * @return array An array containing a 'response' element which will be the SNMP response or an 'error' element which will be the error message returned
     */
    public function getSnmpAllPortsQuery($ip, $oid, $keyRegEx = '')
    {

        $response = $this->getSnmpAllPortDesc($ip, $keyRegEx);
        if (isset($response['error']))
        {
            return $response;
        }

        $filteredPortIndexArr = $response['response'];
        $response = $this->snmp2_real_walk($ip, $this->readCommunity, $oid);
        if (isset($response['error']))
        {
            return $response;
        }

        $formattedPortArray = $this->formatSnmpResponse($response['response']);
        if ($keyRegEx != '')
        {
            foreach (array_keys($formattedPortArray) as $key)
            {
                if (array_key_exists($key, $filteredPortIndexArr) == false)
                {
                    unset($formattedPortArray[$key]);
                }
            }
        }
        $response['response'] = $formattedPortArray;

        return $response;
    }

    /**
     * [[Description]]
     * @param  string $ip IP address of the switch
     * @param  string $portNum Port number on the switch
     * @param  boolean [$isIdx = false]          is the specified port an interface index?
     * @param  string $oid SNMP OID to run the SNMP walk on
     * @param  boolean [$useBridgeIndex = false] Should we use the bridge port index?
     * @param  boolean [$useSnmpGet = false]     Should we use SNMP get or walk ?
     * @param  boolean [$formatResponse = false] Should we format the SNMP response or return the raw response?
     * @return [[Type]] [[Description]]
     */
    protected function getSnmpIndexValueByPort($ip, $portNum, $isIdx = false, $oid, $useBridgeIndex = false, $useSnmpGet = false, $formatResponse = false)
    {

        $portIndexResponse = array();
        $portIndex = $portNum;

        if ($isIdx == false)
        {
            if ($useBridgeIndex)
            {
                $portIndexResponse = $this->getBridgePortIndex($ip, $portNum, $isIdx);
            } else
            {
                $portIndexResponse = $this->getPortIndex($ip, $portNum, $isIdx);
            }

            if (isset($portIndexResponse['error']))
            {
                return $portIndexResponse;
            }
            $portIndex = $portIndexResponse['response'];
        }
        $response = array();
        if ($useSnmpGet)
        {
            $response = $this->snmp2_real_walk($ip, $this->readCommunity, $oid . '.' . $portIndex, true);
        } else
        {
            $response = $this->snmp2_real_walk($ip, $this->readCommunity, $oid . '.' . $portIndex);
        }
        if ( ! isset($response['error']) && $formatResponse)
        {
            $response['response'] = $this->formatSnmpResponse($response['response']);
        }

        return $response;
    }

    protected function getSnmpIndexValue($ip, $oid, $useSnmpGet = false, $formatResponse = false)
    {

        $response = array();
        if ($useSnmpGet)
        {
            $response = $this->snmp2_real_walk($ip, $this->readCommunity, $oid, true);
        } else
        {
            $response = $this->snmp2_real_walk($ip, $this->readCommunity, $oid);
        }

        if ( ! isset($response['error']) && $formatResponse)
        {
            $response['response'] = $this->formatSnmpResponse($response['response']);
        }

        return $response;
    }

    protected function setSnmpIndexValueByPort($ip, $portNum, $isIdx = false, $useBridgeIndex = false, $oid, $type, $value, $timeout = '1000000', $retries = '5')
    {

        $portIndexResponse = array();
        if ($useBridgeIndex)
        {
            $portIndexResponse = $this->getBridgePortIndex($ip, $portNum, $isIdx);
        } else
        {
            $portIndexResponse = $this->getPortIndex($ip, $portNum, $isIdx);
        }

        if (isset($portIndexResponse['error']))
        {
            return $portIndexResponse;
        }

        return $this->snmp2_set($ip, $this->writeCommunity, $oid . '.' . $portIndexResponse['response'], $type, $value, $timeout, $retries);
    }

    protected function setSnmpIndexValue($ip, $oid, $type, $value, $timeout = '1000000', $retries = '5')
    {

        return $this->snmp2_set($ip, $this->writeCommunity, $oid, $type, $value, $timeout, $retries);
    }

    protected function snmp2_real_walk($ipAddress, $snmpCommunity, $oid, $callSnmp2Get = false)
    {

        $responseArray = ['response' => false];

        if ( ! isset($ipAddress) || $ipAddress == null)
        {
            $responseArray['error'] = 'ip is missing';

            return $responseArray;
        }

        try
        {
            $snmpResponse = false;

            if ($callSnmp2Get)
            {
                $snmpResponse = snmp2_get($ipAddress, $snmpCommunity, $oid);
            } else
            {
                $snmpResponse = snmp2_real_walk($ipAddress, $snmpCommunity, $oid);
            }
            $responseArray['response'] = $snmpResponse;
        } catch (\Exception $e)
        {
            $responseArray['error'] = preg_replace('/snmp2_[^\s]+:\s+/i', '', $e->getMessage());
        }

        return $responseArray;
    }

    protected function snmp2_set($ipAddress, $snmpCommunity, $oid, $type, $value, $timeout = '1000000', $retries = '5')
    {

        $responseArray = ['response' => false];

        if ( ! isset($ipAddress) || $ipAddress == null)
        {
            $responseArray['error'] = 'ip is missing';

            return $responseArray;
        }

        try
        {
            $snmpResponse = false;
            $snmpResponse = snmp2_set($ipAddress, $snmpCommunity, $oid, $type, $value, $timeout, $retries);
            $responseArray['response'] = $snmpResponse;
        } catch (\Exception $e)
        {
            $responseArray['error'] = preg_replace('/snmp2_[^\s]+:\s+/i', '', $e->getMessage());
        }

        return $responseArray;
    }

    protected function filterResponses($responseArray, $regexSearch, $regexReplace)
    {

        if ( ! isset($responseArray['error']))
        {
            foreach ($responseArray as $key => $value)
            {
                $filteredValue = preg_replace($regexSearch, $regexReplace, $value);
                $responseArray[$key] = $filteredValue;
            }
        }

        return $responseArray;
    }

    protected function ticksToTimeArray($inputTimeTicks)
    {

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

    protected function getTimeString($inputTimeTicks)
    {
        if (isset($inputTimeTicks) && $inputTimeTicks != null)
        {
            $timeArray = $this->ticksToTimeArray($inputTimeTicks);
            if (strlen($timeArray['h']) == 1)
            {
                $timeArray['h'] = '0' . $timeArray['h'];
            }
            if (strlen($timeArray['m']) == 1)
            {
                $timeArray['m'] = '0' . $timeArray['m'];
            }
            if (strlen($timeArray['s']) == 1)
            {
                $timeArray['s'] = '0' . $timeArray['s'];
            }

            return $timeArray['d'] . " days, " . $timeArray['h'] . ":" . $timeArray['m'] . ":" . $timeArray['s'];
        }

        return '';
    }

    protected function formatSnmpResponse($snmpResponse, $numOfOidSectionsToKeep = 1)
    {
        if (is_array($snmpResponse))
        {
            $keyPattern = '';
            $keyReplace = '';
            -- $numOfOidSectionsToKeep;
            if ($numOfOidSectionsToKeep > 0)
            {
                $keyPattern = '/.*\.([\w]+\.){' . $numOfOidSectionsToKeep . '}([\w]+)$/';
                $keyReplace = '$1$2';
                //                error_log('formatSnmpResponse(): $keyPattern = ' . $keyPattern);
            } else
            {
                $keyPattern = '/.*\./';
                $keyReplace = '';
                //                error_log('formatSnmpResponse(): $keyPattern = ' . $keyPattern);
            }
            foreach ($snmpResponse as $key => $val)
            {
                $newKey = preg_replace($keyPattern, $keyReplace, $key);
                //                error_log('formatSnmpResponse(): $newKey = ' . $newKey);
                //                $newKey = preg_replace($keyPattern, '', $key);
                $newVal = preg_replace('/.+:/', '', $val);
                $newVal = trim(preg_replace('/"/', '', $newVal));
                unset($snmpResponse[$key]);
                $snmpResponse[$newKey] = $newVal;
            }

            return $snmpResponse;
        } else
        {
            //            error_log('formatSnmpResponse: $snmpResponse NOT an array.');
            if ($snmpResponse != '')
            {
                $snmpRespStr = preg_replace('/.+:/', '', $snmpResponse);
                $snmpRespStr = preg_replace('/"/', '', $snmpRespStr);

                return trim($snmpRespStr);
            }
        }

        return false;
    }

    protected function getMatchingValueEntries($snmpArray, $keyRegEx = '')
    {
        if ($keyRegEx != '')
        {
            foreach ($snmpArray as $key => $val)
            {
                if (preg_match($keyRegEx, $val) === 0)
                {
                    unset($snmpArray[$key]);
                }
            }
        }

        return $snmpArray;
    }

    protected function rejectMatchingValueEntries($snmpArray, $keyRegEx = '')
    {
        if ($keyRegEx != '')
        {
            foreach ($snmpArray as $key => $val)
            {
                if (preg_match($keyRegEx, $val) !== 0)
                {
                    unset($snmpArray[$key]);
                }
            }
        }

        return $snmpArray;
    }

    protected function strpos_all($haystack, $needle, $addValueToEachElement = 0)
    {

        $offset = 0;
        $allpos = array();
        while (($pos = strpos($haystack, $needle, $offset)) !== false)
        {
            $offset = $pos + 1;
            $allpos[] = $pos + $addValueToEachElement;
        }

        return $allpos;
    }

    /**
     *    Stores data in the session.
     *    Example: $instance->foo = 'bar';
     *
     * @param    name    Name of the datas.
     * @param    value    Your datas.
     * @return    void
     * */
    public function __set($name, $value)
    {
        $this->switch[$name] = $value;
    }

    /**
     *    Gets datas from the session.
     *    Example: echo $instance->foo;
     *
     * @param    name    Name of the datas to get.
     * @return    mixed    Datas stored in session.
     * */
    public function __get($name)
    {
        if (isset($this->switch[$name]))
        {
            return $this->switch[$name];
        }
    }

    public function __isset($name)
    {
        return isset($this->switch[$name]);
    }

    public function __unset($name)
    {
        unset($this->switch[$name]);
    }

}

?>
