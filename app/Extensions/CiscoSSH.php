<?php

namespace App\Extensions;

// This class makes use of the phpseclib 0.3.0 SSH2 implimentation.
// The following lines are required.
//set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib-0.3.1_hotfix');
use phpseclib\Net\SSH2;
// define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);


class CiscoSSH {

    private $_hostname;
    private $_username;
    private $_password;
    private $_ssh;
    private $_prompt;
    private $_data;
    private $_connected;
    
    const NET_SSH2_READ_REGEX = 2;

    public function __construct($hostname, $username, $password) {
        $this->_hostname = $hostname;
        $this->_username = $username;
        $this->_password = $password;
        $this->_connected = false;
    }

    public function connect() {

        $this->_ssh = new SSH2($this->_hostname);
        $this->_connected = $this->_ssh->login($this->_username, $this->_password);

        if ($this->_connected) {
            $this->_prompt = $this->_ssh->read('/.*[>|#]/', self::NET_SSH2_READ_REGEX);
            $this->_prompt = str_replace("\r\n", '', trim($this->_prompt));  // fix for inconsistent behavior in IOS
            $this->_ssh->write("terminal length 0\n");
            $this->_ssh->read($this->_prompt);
        }
        return $this->_connected;
    }

    public function isTimeout(){
        return $this->_ssh->isTimeout();
    }
    
    public function setTimeout($seconds){
        if($this->_connected){
            return $this->_ssh->setTimeout($seconds);    
        }
        return false;
    }
    
    
    public function exec($cmd) {
        $this->_data = false;
        $this->_ssh->write($cmd . "\n");
        $this->_data = $this->_ssh->read($this->_prompt);
        if (strpos($this->_data, '% Invalid input detected') !== false) $this->_data = false;

        return $this->_data;
    }
    
    

    public function enable($password) {
        $this->_ssh->write("enable\n");
        $this->_ssh->read('Password:');
        $this->_ssh->write($password . "\n");
        $this->_prompt = $this->_ssh->read('/.*[>|#]/', NET_SSH2_READ_REGEX);
        $this->_prompt = str_replace("\r\n", '', trim($this->_prompt));  // fix for inconsistent behavior in IOS

        if (strpos($this->_prompt, '#') === false) return false;
        else return true;

    }

    public function close() {
        $this->_ssh->write("quit\n");
        unset($this->_ssh);
    }

    public function show_int_status() {

        $result = array();

        $this->exec('show int status');

        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 2; $i++) array_shift($this->_data);
        array_pop($this->_data);

        $pos = strpos($this->_data[0], "Status");
        foreach ($this->_data as $entry) {
            $temp = trim($entry);
            if (strlen($temp) > 1 && $temp[2] != 'r' && $temp[0] != '-') {
                $entry = array();
                $entry['interface'] =  substr($temp, 0, strpos($temp, ' '));
                $entry['description'] = trim(substr($temp, strpos($temp, ' ') + 1, 
                                                    $pos - strlen($entry['interface']) - 1));
                $temp = substr($temp, $pos);
                $temp = sscanf($temp, "%s %s %s %s %s %s");
                $entry['status'] = $temp[0];
                $entry['vlan'] = $temp[1];
                $entry['duplex'] = $temp[2];
                $entry['speed'] = $temp[3];
                $entry['type'] = trim($temp[4] . ' ' . $temp[5]);
                array_push($result, $entry);
            } // if
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function show_run() {

        // Enabled Only
        if (strpos($this->_prompt, '#') === false) die('Error: User must be enabled to use show_int_config()' . "\n");
        $this->exec('show run');
        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 5; $i++) array_shift($this->_data);
        for ($i = 0; $i < 2; $i++) array_pop($this->_data);
        $this->_data = implode("\n", $this->_data);
        return $this->_data;

    }
    
    public function show_start() {
        // Enabled Only
        if (strpos($this->_prompt, '#') === false) die('Error: User must be enabled to use show_int_config()' . "\n");
        $this->exec('show startup-config');
        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 5; $i++) array_shift($this->_data);
        for ($i = 0; $i < 2; $i++) array_pop($this->_data);
        $this->_data = implode("\n", $this->_data);
        return $this->_data;
    }
    
    public function show_log() {

        // Enabled Only
        if (strpos($this->_prompt, '#') === false) die('Error: User must be enabled to use show_log()' . "\n");

        $result = array();

        $this->exec('sh log | inc %');

        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);

        foreach ($this->_data as $entry) {
            $temp = trim($entry);
            $entry = array();
            $entry['timestamp'] = substr($temp, 0, strpos($temp, '%') - 2);
            if ($entry['timestamp'][0] == '.' || $entry['timestamp'][0] == '*')
                $entry['timestamp'] = substr($entry['timestamp'], 1);
            $temp = substr($temp, strpos($temp, '%') + 1);
            $entry['type'] = substr($temp, 0,  strpos($temp, ':'));
            $temp = substr($temp, strpos($temp, ':') + 2);
            $entry['message'] = $temp;
            array_push($result, $entry);
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function show_int($int) {
        $result = array();

        $this->exec('show int ' . $int);

        $this->_data = explode("\r\n", $this->_data);

        foreach ($this->_data as $entry) {
            $entry = trim($entry);
            if (strpos($entry, 'line protocol') !== false) {
                $result['interface'] = substr($entry, 0, strpos($entry, ' '));
                if (strpos($entry, 'administratively') !== false) {
                    $result['status'] = 'disabled';
                } elseif (substr($entry, strpos($entry, 'line protocol') + 17, 2) == 'up') {
                    $result['status'] = 'connected';
                } else {
                    $result['status'] = 'notconnect';
                } // if .. else
            } elseif (strpos($entry, 'Description: ') !== false) {
                $entry = explode(':', $entry);
                $result['description'] = trim($entry[1]);
            } elseif (strpos($entry, 'MTU') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['mtu'] = $entry[0][1];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['bandwidth'] = $entry[1][1];
                $entry[2] = trim($entry[2]);
                $entry[2] = explode(' ', $entry[2]);
                $result['dly'] = $entry[2][1];
            } elseif (strpos($entry, 'duplex') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $entry[0][0] = explode('-', $entry[0][0]);
                $result['duplex'] = strtolower($entry[0][0][0]);
                $entry[1] = trim($entry[1]);
                if (strpos($entry[1], 'Auto') !== false) {
                    $result['speed'] = 'auto';
                } else {
                    $result['speed'] = intval($entry[1]);
                } // if .. else
                $entry[2] = rtrim($entry[2]);
                $result['type'] = substr($entry[2], strrpos($entry[2], ' ') + 1);
            } elseif (strpos($entry, 'input rate') !== false) {
                $entry = explode(',', $entry);
                $result['in_rate'] = substr($entry[0], strpos($entry[0], 'rate') + 5,
                                            strrpos($entry[0], ' ') - (strpos($entry[0], 'rate') + 5));
                $entry = trim($entry[1]);
                $entry = explode(' ', $entry);
                $result['in_packet_rate'] = $entry[0];
            } elseif (strpos($entry, 'output rate') !== false) {
                $entry = explode(',', $entry);
                $result['out_rate'] = substr($entry[0], strpos($entry[0], 'rate') + 5,
                                             strrpos($entry[0], ' ') - (strpos($entry[0], 'rate') + 5));
                $entry = trim($entry[1]);
                $entry = explode(' ', $entry);
                $result['out_packet_rate'] = $entry[0];
            } elseif (strpos($entry, 'packets input') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['in_packet'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['in'] = $entry[1][0];
                if (count($entry) > 2) {
                    $entry[2] = trim($entry[2]);
                    $entry[2] = explode(' ', $entry[2]);
                    $result['no_buffer'] = $entry[2][0];
                } // if
            } elseif (strpos($entry, 'Received') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['broadcast'] = $entry[0][1];
                if (count($entry) > 1) {
                    $entry[1] = trim($entry[1]);
                    $entry[1] = explode(' ', $entry[1]);
                    $result['runt'] = $entry[1][0];
                    $entry[2] = trim($entry[2]);
                    $entry[2] = explode(' ', $entry[2]);
                    $result['giant'] = $entry[2][0];
                    $entry[3] = trim($entry[3]);
                    $entry[3] = explode(' ', $entry[3]);
                    $result['throttle'] = $entry[3][0];
                } // if
            } elseif (strpos($entry, 'CRC') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['in_error'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['crc'] = $entry[1][0];
                $entry[2] = trim($entry[2]);
                $entry[2] = explode(' ', $entry[2]);
                $result['frame'] = $entry[2][0];
                $entry[3] = trim($entry[3]);
                $entry[3] = explode(' ', $entry[3]);
                $result['overrun'] = $entry[3][0];
                $entry[4] = trim($entry[4]);
                $entry[4] = explode(' ', $entry[4]);
                $result['ignored'] = $entry[4][0];
            } elseif (strpos($entry, 'watchdog') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['watchdog'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['multicast'] = $entry[1][0];
                if (count($entry) > 2) {
                    $entry[2] = trim($entry[2]);
                    $entry[2] = explode(' ', $entry[2]);
                    $result['pause_in'] = $entry[2][0];
                } // if
            } elseif (strpos($entry, 'dribble') !== false) {
                $entry = trim($entry);
                $entry = explode(' ', $entry);
                $result['in_dribble'] = $entry[0];
            } elseif (strpos($entry, 'packets output') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['out_packet'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['out'] = $entry[1][0];
                $entry[2] = trim($entry[2]);
                $entry[2] = explode(' ', $entry[2]);
                $result['underrun'] = $entry[2][0];
            } elseif (strpos($entry, 'output errors') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['out_error'] = $entry[0][0];
                if (count($entry) > 2) {
                    $entry[1] = trim($entry[1]);
                    $entry[1] = explode(' ', $entry[1]);
                    $result['collision'] = $entry[1][0];
                    $entry[2] = trim($entry[2]);
                    $entry[2] = explode(' ', $entry[2]);
                    $result['reset'] = $entry[2][0];
                } else {
                    $entry[1] = trim($entry[1]);
                    $entry[1] = explode(' ', $entry[1]);
                    $result['reset'] = $entry[1][0];
                } // if .. else
            } elseif (strpos($entry, 'babbles') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['babble'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['late_collision'] = $entry[1][0];
                $entry[2] = trim($entry[2]);
                $entry[2] = explode(' ', $entry[2]);
                $result['deferred'] = $entry[2][0];
            } elseif (strpos($entry, 'lost carrier') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['lost_carrier'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['no_carrier'] = $entry[1][0];
                if (count($entry) > 2) {
                    $entry[2] = trim($entry[2]);
                    $entry[2] = explode(' ', $entry[2]);
                    $result['pause_out'] = $entry[2][0];
                } // if
            } elseif (strpos($entry, 'output buffer failures') !== false) {
                $entry = explode(',', $entry);
                $entry[0] = trim($entry[0]);
                $entry[0] = explode(' ', $entry[0]);
                $result['out_buffer_fail'] = $entry[0][0];
                $entry[1] = trim($entry[1]);
                $entry[1] = explode(' ', $entry[1]);
                $result['out_buffer_swap'] = $entry[1][0];
            } // if .. elseif
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function show_int_config($int) {

        // Enabled Only
        if (strpos($this->_prompt, '#') === false) die('Error: User must be enabled to use show_int_config()' . "\n");

        $this->exec('show run int ' . $int);

        $this->_data = explode("\r\n", $this->_data);

        for ($i = 0; $i < 5; $i++) array_shift($this->_data);
        for ($i = 0; $i < 2; $i++) array_pop($this->_data);

        $this->_data = implode("\n", $this->_data);

        return $this->_data;

    }

    public function trunk_ports() {

        $result = array();

        $this->exec('show interface status | include trunk');

        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);

        if (count($this->_data) > 0) {
            foreach ($this->_data as $interface) {
                $interface = explode(' ', $interface);
                array_push($result, $interface[0]);
            } // foreach
        } // if
        $this->_data = $result;

        return $this->_data;

    }

    public function vlans() {

        $result = array();

        $this->exec('show spanning-tree summary | include ^VLAN');

        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);

        if (count($this->_data) > 0) {
            foreach ($this->_data as $vlan) {
                $vlan = explode(" ", $vlan);
                $vlan = substr($vlan[0], 4);
                array_push($result, intval($vlan));
            } // foreach
        } // if
        $this->_data = $result;

        return $this->_data;

    }

    public function errdisabled() {

        $result = array();

        $this->exec('show int status err');

        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 2; $i++) array_shift($this->_data);
        array_pop($this->_data);

        $pos = strpos($this->_data[0], "Status");
        foreach ($this->_data as $entry) {
            $temp = trim($entry);
            if (strlen($temp) > 1 && $temp[2] != 'r') {
                $entry = array();
                $entry['interface'] =  substr($temp, 0, strpos($temp, ' '));
                $entry['description'] = trim(substr($temp, strpos($temp, ' ') + 1,
                                                    $pos - strlen($entry['interface']) - 1));
                $temp = substr($temp, $pos);
                $temp = sscanf($temp, "%s %s");
                $entry['status'] = $temp[0];
                $entry['reason'] = $temp[1];
                array_push($result, $entry);
            } // if
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function dhcpsnoop_bindings() {

        $result = array();

        $this->exec('sh ip dhcp snoop binding | inc dhcp-snooping');

        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);

        foreach ($this->_data as $entry) {
            $temp = sscanf($entry, "%s %s %s %s %s %s");
            $entry = array();
            $entry['mac_address'] = $temp[0];
            $entry['mac_address'] = strtolower(str_replace(':', '', $entry['mac_address']));
            $entry['ip_address'] = $temp[1];
            $entry['lease'] = $temp[2];
            $entry['vlan'] = $temp[4];
            $entry['interface'] = $temp[5];
            if ($temp[3] == 'dhcp-snooping') array_push($result, $entry);
        }
        $this->_data = $result;

        return $this->_data;

    }

    public function mac_address_table() {

        $result = array();

        $omit = $this->trunk_ports();
        $this->exec('show mac address-table | exclude CPU');

        $this->_data = str_replace("          ", "", $this->_data);

        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 6; $i++) array_shift($this->_data);
        for ($i = 0; $i < 2; $i++) array_pop($this->_data);

        foreach ($this->_data as $entry) {
            $temp = sscanf($entry, "%s %s %s %s");
            $entry = array();
            $entry['mac_address'] = $temp[1];
            $entry['interface'] = $temp[3];
            if (in_array($entry['interface'], $omit) == false) {
                array_push($result, $entry);
            } // if
        } // foreach

        $this->_data = $result;

        return $this->_data;

    }

    public function arp_table() {

        $result = array();

        $this->exec('show arp | exc Incomplete');

        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 2; $i++) array_shift($this->_data);
        array_pop($this->_data);

        foreach ($this->_data as $entry) {
            $temp = sscanf($entry, "%s %s %s %s %s %s");
            $entry = array();
            $entry['ip'] = $temp[1];
            $entry['mac_address'] = $temp[3];
            if ($temp[2] == '-') $temp[2] = '0';
            $entry['age'] = $temp[2];
            $entry['interface'] = $temp[5];
            if ($entry['ip'] != 'Address' && $entry['mac_address'] != 'Incomplete') {
                array_push($result, $entry);
            } // if
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function ipv6_neighbor_table() {

        $result = array();

        $this->exec('show ipv6 neighbors | exc INCMP');

        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 2; $i++) array_shift($this->_data);
        for ($i = 0; $i < 2; $i++) array_pop($this->_data);

        foreach ($this->_data as $entry) {
            $temp = sscanf($entry, "%s %s %s %s %s");
            $entry = array();
            $entry['ipv6'] = $temp[0];
            $entry['mac_address'] = $temp[2];
            $entry['age'] = $temp[1];
            $entry['interface'] = $temp[4];
            array_push($result, $entry);
        } // foreach
        $this->_data = $result;

        return $this->_data;

    }

    public function ipv6_routers() {

        $result = array();

        $this->exec('show ipv6 routers');

        $this->_data = explode("\r\n", $this->_data);
        array_shift($this->_data);
        array_pop($this->_data);

        for ($i = 0; $i < count($this->_data); $i++) {
            $entry = trim($this->_data[$i]);
            if (substr($entry, 0, 7) == 'Router ') {
                $temp = sscanf($entry, "%s %s %s %s");
                $entry = array();
                $entry['router'] = $temp[1];
                $entry['interface'] = str_replace(',', '', $temp[3]);
                $temp = sscanf(trim($this->_data[$i + 4]), "%s %s %s");
                $entry['prefix'] = $temp[1];
                $i = $i + 5;
                array_push($result, $entry);
            } // if
        } // for
        $this->_data = $result;

        return $this->_data;

    }

    public function configure($config) {

        // USE AT OWN RISK: This function will apply configuration statements to a device.
        // Enabled Only
        if (strpos($this->_prompt, '#') === false) die('Error: User must be enabled to use configure()' . "\n");

        $this->_data = explode("\n", $config);

        $this->_ssh->write("config t\n");
        $config_prompt = $this->_ssh->read('/.*[>|#]/', NET_SSH2_READ_REGEX);
        $config_prompt = str_replace("\r\n", '', trim($config_prompt));
        if (strpos($config_prompt, 'config)#') !== false) {
            foreach ($this->_data as $c) $this->_ssh->write($c . "\n");
            $this->_ssh->write("end\n");
        }

        $result = $this->_ssh->read($this->_prompt);
        $result = explode("\r\n", $result);
        if (count($this->_data) == (count($result) - 2)) return true;
        else die('Error: Switch rejected configuration: ' . "\n" . $config . "\n");

    }

    public function write_config() {

        $this->exec('write');
        if (strpos($this->_data, '[OK]') !== false) return true;
        else return false;

    }

    public function copy($src, $dest) {

        $ip = '';
        $destFileName = '';
        $result = array();
        $this->exec('copy '.$src.' '.$dest);
        $this->_data = explode("\r\n", $this->_data);

        $numOfLines = count($this->_data);
        $lastLine = $this->_data[$numOfLines - 1];
        $matches = array();
        if(preg_match('/.*\[(.*)\]\?/', $lastLine, $matches) === 1){
            $ip = $matches[1];
            $this->_ssh->write("\n");
            $this->_data = $this->_ssh->read($this->_prompt);
            if(preg_match('/.*\[(.*)\]\?/', $this->_data, $matches) === 1){
                $destFileName = $matches[1];
                $this->_ssh->write("\n");
                $this->_data = $this->_ssh->read($this->_prompt);
                $result = explode("\r\n", $this->_data);
            }

            if(count($result) >= 4){
                if(preg_match('/.*bytes copied.*/', $result[2], $matches) === 1){
                    return 'Success';
                }
                array_pop($result);
                return 'Error: '.implode('\n',$result);
            }   
        }

        return 'Unknown Error: '.$this->_data;
    }


}
