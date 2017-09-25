<?php

namespace App\Extensions\Backup;

use App\Extensions\CiscoSSH;
use App\Models\Portal\NetworkNode;
use App\Models\Portal\BackupLog;
use App\Extensions\CiscoSwitch;
use \Carbon\Carbon;
use \Schema;
use DB;

/**
 * Cisco backup class
 */
class CiscoSwitchBackup implements BackupInterface
{
    public function __construct(){
        DB::connection()->enableQueryLog();
    }

    protected function isBackupRunning(){
        $runningBackupProcessesArray = BackupLog::where('status','running')
            ->get()
            ->toArray();
        return  (count($runningBackupProcessesArray) > 0) ? true : false;
    }

    protected function updateProgress($progress, $details = ''){
        if($details == ''){
            error_log('backup progress: '. $progress);
        } else {
            error_log('backup progress: '. $details);
        }
        return true;
    }

    protected function updateErrors($error, $details = ''){
        if($details == ''){
            error_log('backup error: '. $error);
        } else {
            error_log($details);
        }
        return true;
    }

    protected function updateBackupLog($id, $attributesArray){
        if(count($attributesArray) == 0){ 
            return false;
        }

        $logRecord = BackupLog::findOrNew($id);
        foreach($attributesArray as $key => $value){
            if($key == 'errors'){
                if($logRecord->errors == ''){
                    $logRecord->errors = json_encode(array($value));
                } else {
                    $errorArray = json_decode($logRecord->errors);
                    $errorArray[] = $value;
                    $logRecord->errors = json_encode($errorArray);    
                }
                continue;
            }
            if(Schema::hasColumn($logRecord->getTable(), $key)){
                $logRecord->{$key} = $value;
            }
        }
        $logRecord->save();
        return $logRecord->id;
    }

    public function backupPreCheck(){
        if($this->isBackupRunning()){
            $this->updateProgress('ignored');
            $this->updateErrors('Notice: There is already a backup running');
            return 'running';
        }
        return 'ok';
    }

    public function backupAll(){
        if($this->isBackupRunning()){
            $this->updateProgress('ignored');
            $this->updateErrors('Notice: There is already a backup running');
            return 'ignored';
        }

        $netNodes = NetworkNode::with('serviceLocation')
            ->where('Vendor','Cisco')
            //            ->take(4)
            ->get()
            ->toArray();

        $progressCount = 1;
        $successCount = 0;
        $backupLogId = -1;
        $progress = '';
        $status = '';
        $nodeCount = count($netNodes);
        $switch = new CiscoSwitch;

        if($nodeCount > 0){

            $status = 'running';
            $backupLogId = $this->updateBackupLog($backupLogId,['job_name' => 'All Switch Backup','status' => $status]);

            // Must disable error reporting otherwise Laravel will throw exceptions
            // on errors and halt code execution
            error_reporting(0);

            foreach($netNodes as $node){

                // DEBUGGING and TESTING
                //                sleep(2);
                //                if($progressCount == $nodeCount){
                //                    $status = 'complete';
                //                    $progress = 'complete';
                //                } else {
                //                    $progress = $progressCount.'/'.$nodeCount;                    
                //                }
                //                $this->updateBackupLog($backupLogId, ['status' => $status, 'details' => 'Backed up '.$progressCount.' of '.$nodeCount]);
                //                $this->updateProgress($request, $progress, 'Backed up '.$node['HostName']. ' ('.$node['IPAddress'].')');
                //                $progressCount++;
                //                continue;

                $ip = $node['IPAddress'];
                $timestampFormat = 'd-M-Y-h.i.s';
                $timestamp =  date ($timestampFormat);

                // Get the current hostname of the device
                $snmpHostname = CiscoSwitch::formatSnmpResponse($switch->getSnmpSysName($ip));
                $snmpHostname = preg_replace('/\..*/','',$snmpHostname);
                $hostname = $snmpHostname;

                if($snmpHostname == ''){
                    $hostname = $node['HostName'];
                }

                $filename = '['.$hostname.']-['.$node['Model'].']-'.$timestamp.'-running.config'; 
                $tmpfileName = $hostname.'.config';
                $tftproot = '/tftpboot';

                if(file_exists($tftproot.'/'.$tmpfileName) == true){
                    unlink($tftproot.'/'.$tmpfileName);
                }
                touch($tftproot.'/'.$tmpfileName);
                chmod($tftproot.'/'.$tmpfileName, 0777);

                $cisco = new CiscoSSH($ip, 'portal', 'test');
                $connected = $cisco->connect();
                $progressDetail = '';
                $logDetails = array();

                if($connected){
                    $cisco->setTimeout(60);
                    $runningConf = $cisco->show_run(); 

                    // If backup mode is TFTP you can do the following:
                    // $data = $cisco->copy('running-config', 'tftp://10.11.101.227/'.$tmpfileName);

                    if($cisco->isTimeout() == false){
                        //                        $destination = '/home/backups/'.$filename;
                        $destination = '/tmp/'.$filename;
                        $backupFile = fopen($tftproot.'/'.$tmpfileName, "w");
                        if($backupFile == false){
                            error_log("Unable to open file: ".$tftproot.'/'.$tmpfileName);
                            continue;
                        }
                        fwrite($backupFile, $runningConf);
                        fclose($backupFile);
                        rename($tftproot.'/'.$tmpfileName, $destination);
                        chmod($destination, 0755);

                        // $progress = number_format($i * 100 / $nodeCount);
                        $successCount++; 
                        $progressDetail = 'Backed up '.$hostname. ' ('.$ip.')';
                        $logDetails = array('details' => 'Backed up '.$successCount.' of '.$nodeCount);
                    } else {
                        $progressDetail = 'Timed out backing up '.$hostname. ' ('.$ip.'): skipping';
                        $logDetails = array('errors' => 'Timed out backing up '.$hostname. ' ('.$ip.'): skipping');
                        $this->updateErrors('Timed out backing up '.$hostname. ' ('.$ip.'): skipping');
                        //                        $this->updateErrors($request, 'Timed out backing up '.$hostname. ' ('.$ip.'): skipping');
                    }
                    $cisco->close();
                    $progress = $progressCount.'/'.$nodeCount;
                } else {
                    $progressDetail = 'Could not connect to '.$hostname. ' ('.$ip.'): skipping';
                    $logDetails = array('errors' => 'Could not connect to '.$hostname. ' ('.$ip.'): skipping');
                    $this->updateErrors('Could not connect to '.$hostname. ' ('.$ip.'): skipping');
                    //$this->updateErrors($request, 'Could not connect to '.$hostname. ' ('.$ip.'): skipping');
                }

                $this->updateBackupLog($backupLogId, $logDetails);
                $this->updateProgress($progress, $progressDetail);
                //                $this->updateProgress($request, $progress, $progressDetail);
                $progressCount++;
            }
        } else {
            $progress = 'complete';
            $backupLogId = $this->updateBackupLog(-1,['status' => $progress, 'details' => 'There were no nodes to back up']);
            $this->updateProgress($progress, 'There were no nodes to back up');
            //            $this->updateProgress($request, $progress, 'There were no nodes to back up');
        }

        $this->updateBackupLog($backupLogId, ['status' => 'complete', 'completed' => Carbon::now()->toDateTimeString()]);
        $this->updateProgress('complete');
        //        $this->updateProgress($request, 'complete');
        return 'done';
    }

    public function getBackupStatus(){

    }

}
