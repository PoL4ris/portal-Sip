<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;
use App\Extensions\BillingHelper;
use App\Extensions\MtikRouter;
use App\Extensions\CiscoSSH;
use Illuminate\Support\Facades\File;
use Storage;
use App\Models\NetworkNode;
use App\Models\CustomerPort;
use App\Models\Invoice;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use SendMail;


class GeneralTasks extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:general-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the general task function of the DataMigrationUtils class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting general task');

//        $this->activateSnmpSystemShutdown();

//        $this->testCiscoSshAccess();

//        $this->fixMikrotikHotspotProfile();
//        $this->getMikrotikHotspotProfiles();
//        $this->getMikrotikHotspots();

        $this->sendMassEmail();
//        $this->rebootMikrotik();
//        $this->uploadMikrotikPackageFiles();
//        $this->cleanupBadCustomerPorts();

//        $billingHelper = new BillingHelper();
//        $billingHelper->processPendingAutopayInvoicesByMonth('August');

//        $billingHelper->processInvoice($invoice, true, false, true);
//        $invoice = Invoice::find(4943);
//        dd($invoice);
//        $billingHelper->processInvoice($invoice, true, false, true);

//        $billingHelper->generateResidentialChargeRecordsByMonth('September', true, false);
//        $billingHelper->generateResidentialChargeRecordsByMonth('September', true, false);
//        $billingHelper->invoicePendingAutoPayChargesByMonth('September');

//        $result = $billingHelper->processPendingAutopayInvoices();
//        $dbMigrationUtil = new DataMigrationUtils(true);
//        $dbMigrationUtil->generalDatabaseTask();
//        $this->updateMikrotikHotspotLoginFiles();

        $this->info('Done');

    }

    protected function activateSnmpSystemShutdown()
    {

        $switchList = ['10.11.254.103', '10.11.254.123', '10.11.254.140', '10.11.254.107', '10.11.254.171'];
//        $switchList = ['10.11.188.10'];

        foreach ($switchList as $switch)
        {
            echo $switch . ': ';
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.2.665 i 1`;
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.3.665 i 1`;
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.4.665 i 4`;
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.5.665 a 10.11.101.116`;
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.6.665 s cisco_snmp_server_system_shutdown.txt`;
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.9.96.1.1.1.1.14.665 i 1`;

            /*
             * When ready to reboot, comment the above lines and uncomment the below
             */
//            `snmpset -c BigSeem -v 2c $switch 	 1.3.6.1.4.1.9.2.9.9.0 i 2`;

            sleep(1);
            echo "done\n";
        }

    }

    protected function testCiscoSshAccess()
    {

//        App::error(function(Exception $exception){
//            Log::error($exception);
//            if(($exception->getMessage() == "Connection closed by server") &&
//                ($exception->getFile() == "/home/{user}/{location}/deploy/vendor/phpseclib/phpseclib/phpseclib/Net/SSH2.php")){
//                // Let's handle this
//            }
//        });

        error_reporting(0);

        $coreSwitches = NetworkNode::where('id_types', config('const.type.switch'))
//            ->where('ip_address', '10.11.188.10')
            ->where('vendor', 'Cisco')
            ->where('model', 'like', 'WS-C3560G%')
            ->get();


//        dd($coreSwitches->pluck('host_name'));

        $uniqueCoreSwitches = $coreSwitches->unique('ip_address');

        $results = [];
        foreach ($uniqueCoreSwitches as $switch)
        {
            echo $switch->host_name . ': ';

            $cisco = new CiscoSSH($switch->ip_address, 'portal', 'test');
            $connected = $cisco->connect();
            if ($connected)
            {
                echo "ok\n";

            } else
            {
                echo "BAD\n";
                $results[] = $switch->ip_address;
            }
            $cisco->close();
        }

        dd($results);

        /**
         *  Other things you can do with the CiscoSSH class
         */
//        $data = $cisco->show_int_config('f0/43');
//        $data = $cisco->exec('copy running-config tftp://10.11.101.227/130Canal-swi-3-running.config');
//        $data = $cisco->exec('show startup');
//        touch("/tftpboot/test.txt");
//        $data = $cisco->copy('running-config', 'tftp://10.11.101.227/130Canal-swi-3-running.config');

    }

    protected function updateMikrotikHotspotLoginFiles()
    {
        echo 'exiting';

        return true;

        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//        $mikrotiks = NetworkNode::where('id',1573)->get();

        $localFile = storage_path('app/login.html');
        $remoteFile = 'hotspot/login.html';

        foreach ($mikrotiks as $mikrotik)
        {
            echo 'Updating ' . $mikrotik->host_name . '(' . $mikrotik->ip_address . '): ';

            try
            {
                $ftp = new FtpClient();
                $ftp->connect($mikrotik->ip_address, false, 2121);
                $ftp->login('admin', 'BigSeem');
                $ftp->pasv(true);

                if ($this->fileExists($ftp, $remoteFile) == false)
                {
                    echo 'failed: file not found' . "\n";
                    continue;
                }

                $xferSuccessful = $ftp->put($remoteFile, $localFile, FTP_BINARY);
                if ($xferSuccessful)
                {
                    echo "ok\n";
                    continue;
                }
            } catch (FtpException $e)
            {
                echo 'failed: ' . $e->getMessage() . "\n";
            }
        }
    }

    protected function uploadMikrotikPackageFiles()
    {
        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//            ->where('role', 'Transit')->get();
//        $mikrotiks = NetworkNode::where('id', 800)->get();

//        dd($mikrotiks->pluck('host_name'));

        $uniqueMikrotiks = $mikrotiks->unique('ip_address');

        foreach ($uniqueMikrotiks as $mikrotik)
        {
            $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
                                             'username'   => config('netmgmt.mikrotik.username'),
                                             'password'   => config('netmgmt.mikrotik.password')]);
            $softwareVersion = $serviceRouter->getSoftwareVersion($mikrotik->ip_address);
            $architecture = $serviceRouter->getArchitecture($mikrotik->ip_address);

            // Skip non-CCR architectures
            if ($architecture != 'tile')
            {
                continue;
            }

            // Skip CCRs that already have 6.40.1
            if (preg_match('/6\.40\.1.*/', $softwareVersion) === 1)
            {
                continue;
            }

            echo 'Uploading files to ' . $mikrotik->host_name . ' (' . $mikrotik->ip_address . '): ';

            try
            {
                $ftp = new FtpClient();
                $ftp->connect($mikrotik->ip_address, false, 2121);
                $ftp->login('admin', 'BigSeem');
                $ftp->pasv(true);

                $directory = storage_path('app/mikrotik_firmware/all_packages-tile-6.40.1');
                $packageFiles = File::allFiles($directory);

                foreach ($packageFiles as $packageFile)
                {


                    $localFile = $packageFile->getPathname();
                    $remoteFile = $packageFile->getFilename();

                    $xferSuccessful = $ftp->put($remoteFile, $localFile, FTP_BINARY);
                    if ($xferSuccessful)
                    {
                        echo '.';
                    } else
                    {
                        echo '!';
                    }
                }
                echo "  done\n";
            } catch (FtpException $e)
            {
                echo 'failed: ' . $e->getMessage() . "\n";
            }
        }
    }

    protected function rebootMikrotik()
    {
//        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//            ->where('role', 'Transit')->get();
        $mikrotiks = NetworkNode::where('id', 1117)->get();

        dd($mikrotiks->pluck('host_name'));

        $uniqueMikrotiks = $mikrotiks->unique('ip_address');

        foreach ($uniqueMikrotiks as $mikrotik)
        {
            $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
                                             'username'   => config('netmgmt.mikrotik.username'),
                                             'password'   => config('netmgmt.mikrotik.password')]);

            echo 'Rebooting ' . $mikrotik->host_name . ' (' . $mikrotik->ip_address . '): ';

            $serviceRouter->reboot($mikrotik->ip_address);

            echo "  done\n";
        }
    }

    protected function fixMikrotikHotspotProfile()
    {
//        $ipAddresses = ['10.11.137.1', '10.11.127.1', '10.11.117.1', '10.11.118.1', '10.11.119.1', '10.11.120.1', '10.11.9.1', '10.11.134.1', '10.11.142.1', '10.11.7.1', '10.11.147.1', '10.11.153.1', '10.11.154.1', '10.11.152.1', '10.11.157.1', '10.11.135.1', '10.11.174.1', '10.11.156.1', '10.11.112.1', '10.11.158.1', '10.11.151.1', '10.11.124.1', '10.11.161.1', '10.11.163.1', '10.11.164.1', '10.11.166.1', '10.11.223.1', '10.11.170.1', '10.11.173.1', '10.11.178.1', '10.11.180.1', '10.11.190.1', '10.11.201.1', '10.11.217.1', '10.11.225.1', '10.11.245.1', '10.11.237.1', '10.11.241.1', '10.11.243.1', '10.11.64.1', '10.11.254.62', '10.11.254.58', '10.11.44.1', '10.11.26.1', '10.11.22.1', '10.11.24.1', '10.11.254.42', '10.11.255.239', '10.15.235.1', '10.15.233.1', '10.15.231.1', '10.15.227.1', '10.15.215.1'];
        $ipAddresses = ['10.10.13.1', '10.11.138.1', '10.10.19.1', '10.11.115.1', '10.10.21.1', '10.11.114.1', '10.10.31.1', '10.11.109.1'];

        foreach ($ipAddresses as $ip)
        {

            $serviceRouter = new MtikRouter(['ip_address' => $ip,
                                             'username'   => config('netmgmt.mikrotik.username'),
                                             'password'   => config('netmgmt.mikrotik.password')]);

            $command = '/ip/hotspot/set';
            $commandOptions = ['.id'     => '*1',
                               'profile' => 'default'];

            echo $ip . ' ... ';
            $results = $serviceRouter->runCommand($ip, $command, $commandOptions);
            echo "done\n";
//            dd('done');
        }
    }

    protected function getMikrotikHotspots()
    {
        echo 'Getting all Miktotik info ... ';
        $command = '/ip/hotspot/print';
        $results = $this->runCommandOnAllMikrotiks($command);
        echo "done\n";

        foreach ($results as $ip => $result)
        {
            echo '(' . $ip . '): ' . "\n";
            if (isset($result['error']))
            {
                echo ' error: ' . $result['error'];
            } else
            {
                foreach ($result['response'] as $hotspot)
                {
                    echo '   ' . $hotspot['name'] . ': ' . $hotspot['profile'] . "\n";
                }
            }
        }
    }

    protected function getMikrotikHotspotProfiles()
    {
        echo 'Getting all Miktotik info ... ';
        $command = '/ip/hotspot/profile/print';
        $results = $this->runMikrotikCommand($command);
        echo "done\n";

        foreach ($results as $ip => $result)
        {
            echo '(' . $ip . '): ' . "\n";
            if (isset($result['error']))
            {
                echo ' error: ' . $result['error'];
            } else
            {
                foreach ($result['response'] as $hotspotProfile)
                {
                    echo '   ' . $hotspotProfile['name'] . "\n";
                }
            }
        }
    }

    protected function runCommandOnAllMikrotiks($command, $commandOptions = [], $resultFilter = [])
    {
        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//            ->where('role', 'Transit')->get();
//        $mikrotiks = NetworkNode::where('id', 1117)->get();

//        dd($mikrotiks->pluck('host_name'));

        $uniqueMikrotiks = $mikrotiks->unique('ip_address');

        $results = [];
        foreach ($uniqueMikrotiks as $mikrotik)
        {
            $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
                                             'username'   => config('netmgmt.mikrotik.username'),
                                             'password'   => config('netmgmt.mikrotik.password')]);

            $results[$mikrotik->ip_address] = $serviceRouter->runCommand($mikrotik->ip_address, $command, $commandOptions);
        }

        return $results;
    }

    protected function fileExists($ftp, $file)
    {
        if ($ftp->size($file) != - 1)
        {
            return true;
        }

        return false;
    }

    protected function cleanupBadCustomerPorts()
    {
        // Records processed count
        $recordsProcessed = 0;
        $badPorts = 0;

        // Total record count
        $totalRecords = CustomerPort::count();

        $runQuery = function ($startingId, $recordsPerCycle) {
            return CustomerPort::where('id', '>', $startingId)
                ->orderBy('id', 'asc')
                ->take($recordsPerCycle)
                ->get();
        };

        $recordsPerCycle = 50;
        $startingId = 14391; // 0

        echo 'Checking ' . $totalRecords . ' records ... ' . "\n";

        while (true)
        {

            $customerPorts = $runQuery($startingId, $recordsPerCycle);

            if ($customerPorts->count() == 0)
            {
                break;
            }

            foreach ($customerPorts as $customerPort)
            {

                $customer = $customerPort->customer;
                if ($customer == null)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing a customer' . "\n";
                    $badPorts ++;
                    $startingId = $customerPort->id;
                    CustomerPort::destroy($customerPort->id);
                    $recordsProcessed ++;
                    continue;
                }
                $customerAddress = $customer->address;

                $port = $customerPort->port;
                if ($customer == null)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing a port' . "\n";
                    $badPorts ++;
                    $startingId = $customerPort->id;
                    CustomerPort::destroy($customerPort->id);
                    $recordsProcessed ++;
                    continue;
                }
                $portAddress = $port->address;

                if ($portAddress == null)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing an address' . "\n";
                    CustomerPort::destroy($customerPort->id);
                    $badPorts ++;
                } else if ($customerAddress != null && $portAddress->code != $customerAddress->code)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' does not match' . "\n";
                    CustomerPort::destroy($customerPort->id);
                    $badPorts ++;
                }

                // Do some accounting
                $startingId = $customerPort->id;
                $recordsProcessed ++;
            }

            // Update the progress bar
//            $this->advanceProgressBar(0, $dataMigration->records_processed);
            usleep(500000);
        }

        echo $badPorts . ' out of ' . $recordsProcessed . ' were bad' . "\n";
    }

    protected function sendMassEmail()
    {

        /**
         * Test email contact list
         */
//        $managersContactInfo = [['first_name' => 'Peyman', 'last_name' => 'Pourkermani', 'email' => 'peyman@pourkermani.com']];


        /**
         * Production email contact list
         */
        $managersContactInfo = [
//            ['first_name' => 'Judy', 'last_name' => 'Pierson', 'email' => '1400museumpark@gmail.com'],
//            ['first_name' => 'Marcy', 'last_name' => 'Juarez', 'email' => 'M.Juarez@dkcondo.com'],
//            ['first_name' => 'Wayne', 'last_name' => 'Springer', 'email' => 'theresidencesof41e8thmgr@draperandkramer.com'],
//            ['first_name' => 'Denise', 'last_name' => 'Wyatt', 'email' => 'dwyatt@lmsnet.com'],
//            ['first_name' => 'Aislinn', 'last_name' => 'Pulley', 'email' => 'apulley@lmsnet.com'],
//            ['first_name' => 'Bradley', 'last_name' => 'Brooks', 'email' => 'mgrlakeside@sudlerchicago.com'],
//            ['first_name' => 'Cindy', 'last_name' => 'Schulz', 'email' => 'c.schulz@dkcondo.com'],
//            ['first_name' => 'Denise', 'last_name' => 'Savino', 'email' => 'manager125@communityspecialists.net'],
//            ['first_name' => 'Diana', 'last_name' => 'Turowski', 'email' => 'DTurowski@lmsnet.com'],
//            ['first_name' => 'Flo', 'last_name' => 'Roberson', 'email' => 'mplmanager@sudlerchicago.com'],
//            ['first_name' => 'Jeff', 'last_name' => 'Steinback', 'email' => 'jsteinback@advantage-management.com'],
//            ['first_name' => 'Kirk', 'last_name' => 'Sullivan', 'email' => 'k.sullivan@dkcondo.com'],
//            ['first_name' => 'Bobby', 'last_name' => 'Kennedy', 'email' => 'Bobby@buildinggroup.com'],
//            ['first_name' => 'Amy', 'last_name' => 'Eickhoff', 'email' => 'Aeickhoff@lmsnet.com'],
//            ['first_name' => 'Joni', 'last_name' => 'Hoffer', 'email' => 'jhoffer@lmsnet.com'],
//            ['first_name' => 'Deborah', 'last_name' => 'Romero', 'email' => 'D.Romero@DKCondo.com'],
//            ['first_name' => 'Yvette', 'last_name' => 'Lafrenere', 'email' => 'ylafrenere@communityspecialists.net'],
//            ['first_name' => 'April', 'last_name' => 'Daly', 'email' => 'A.Daly@dkcondo.com'],
//            ['first_name' => 'Shirley', 'last_name' => 'Feldman', 'email' => 'S.Feldman@dkcondo.com'],
//            ['first_name' => 'Cherie', 'last_name' => 'Schmidt', 'email' => '125east@museumpark.net'],
//            ['first_name' => 'Nanci', 'last_name' => 'Gonzalez', 'email' => 'nanci.gonzales@draperandkramer.com'],
//            ['first_name' => 'Kathleen', 'last_name' => 'Dormin', 'email' => 'k.dormin@dkcondo.com'],
//            ['first_name' => 'Mark', 'last_name' => 'Johnson', 'email' => 'PrintersRowMGR@draperandkramer.com'],
//            ['first_name' => 'Kim', 'last_name' => 'Pinson', 'email' => 'k.pinson@dkcondo.com'],
//            ['first_name' => 'Brent', 'last_name' => 'Lehr', 'email' => 'blehr@amli.com'],
//            ['first_name' => 'Jermeise', 'last_name' => 'Steele', 'email' => 'j.steele@dkcondo.com'],
//            ['first_name' => 'Mickey', 'last_name' => 'Nikezic', 'email' => 'mnikezic@realtymortgageco.com'],
//            ['first_name' => 'Dan', 'last_name' => 'Bisplinghoff', 'email' => 'Daniel.Bisplinghoff@fsresidential.com'],
//            ['first_name' => 'Rajan', 'last_name' => 'Patel', 'email' => 'rpatel@lmsnet.com'],
//            ['first_name' => 'Stephanie', 'last_name' => 'Rosley', 'email' => 'stephanie.rosley@associa.us'],
//            ['first_name' => 'Mollie', 'last_name' => 'Johnstone', 'email' => 'mjohnstone@habitat.com'],
//            ['first_name' => 'Maureen', 'last_name' => 'Tabor', 'email' => 'mtabor@resideliving.com'],
//            ['first_name' => 'Gayle', 'last_name' => 'Lang', 'email' => 'gl@phoenixrisinggroup.com'],
//            ['first_name' => 'Mavis', 'last_name' => 'Mather', 'email' => 'm.mather@dkcondo.com'],
//            ['first_name' => 'David', 'last_name' => 'Westveer', 'email' => 'david@westwardmanagement.com'],
//            ['first_name' => 'Joan', 'last_name' => 'Brachmann', 'email' => 'j.brachmann@dkcondo.com'],
//            ['first_name' => 'Brooke', 'last_name' => 'Linthicum', 'email' => 'thegrantmgr@sudlerchicago.com'],
//            ['first_name' => 'Christina', 'last_name' => 'Hannigan', 'email' => 'joffreytowermgr@sudlerchicago.com'],
//            ['first_name' => 'Mickey', 'last_name' => 'Nikezic', 'email' => 'mnikezic@realtymortgageco.com'],
//            ['first_name' => 'Jennifer', 'last_name' => 'Bastidas', 'email' => 'jennifer.bastidas@fsresidential.com'],
//            ['first_name' => 'Donna', 'last_name' => 'Ciota', 'email' => 'donna.ciota@associa.us'],
//            ['first_name' => 'Sharron', 'last_name' => 'Healy', 'email' => 'shealy@peakproperties.biz'],
//            ['first_name' => 'Ethan', 'last_name' => 'Lewis', 'email' => 'ethan@buildinggroup.com'],
//            ['first_name' => 'Austin', 'last_name' => 'Arkush', 'email' => 'AustinA@westwardmanagement.com'],
//            ['first_name' => 'Sandy', 'last_name' => 'Albecker', 'email' => 'sandy@enlan.com'],
//            ['first_name' => 'Sarah', 'last_name' => 'Florie', 'email' => 'S.Florie@DKCondo.com'],
//            ['first_name' => 'Lily', 'last_name' => 'Godow', 'email' => 'lgodow@burnhampointechicago.com'],
//            ['first_name' => 'Edin', 'last_name' => 'Dzafic', 'email' => 'manager@hermitageonhuron.com'],
//            ['first_name' => 'Jennifer', 'last_name' => 'Taylor', 'email' => 'jtaylor@forthgrp.com'],
//            ['first_name' => 'Lynn', 'last_name' => 'Stephens', 'email' => 'palmolivemgr@sudlerchicago.com'],
//            ['first_name' => 'Serina', 'last_name' => 'Brancato', 'email' => 's.brancato@dkcondo.com'],
//            ['first_name' => 'Susan', 'last_name' => 'Kramer', 'email' => 'thebuckinghammgr@draperandkramer.com'],
//            ['first_name' => 'Linda', 'last_name' => 'Zack-Ivery', 'email' => '235wvanburenmgr@sudlerchicago.com'],
//            ['first_name' => 'Sarah', 'last_name' => 'Phillippe', 'email' => '900@amli.com'],
//            ['first_name' => 'Yvette', 'last_name' => 'Young', 'email' => '1700mgr@sudlerchicago.com'],
//            ['first_name' => 'Mary', 'last_name' => 'Wolf', 'email' => 'm.wolf@dkcondo.com'],
//            ['first_name' => 'Steve', 'last_name' => 'Schantz', 'email' => 'canterburyctapt@aol.com'],
//            ['first_name' => 'Tricia', 'last_name' => 'Conway', 'email' => 'tconway@communityspecialists.net'],
//            ['first_name' => 'Kaitlin', 'last_name' => 'McLearen', 'email' => 'kmclearen@amli.com'],
//            ['first_name' => 'Katelin', 'last_name' => 'Dorsey', 'email' => 'k.dorsey@dkcondo.com'],
//            ['first_name' => 'Craig', 'last_name' => 'Mathis', 'email' => 'cmathis@prmchicago.com'],
//            ['first_name' => 'Jim', 'last_name' => 'Quinnett', 'email' => 'Jim.Quinnett@draperandkramer.com'],
//            ['first_name' => 'Donna', 'last_name' => 'Medrano', 'email' => 'dmedrano@advantage-management.com'],
//            ['first_name' => 'Kiyah', 'last_name' => 'Larkins', 'email' => 'k.larkins@draperandkramer.com'],
//            ['first_name' => 'Eric', 'last_name' => 'Ruby', 'email' => 'eruby@advantage-management.com'],
//            ['first_name' => 'Theo', 'last_name' => 'Hodges', 'email' => 'theedge@communityspecialists.net'],
//            ['first_name' => 'Amy', 'last_name' => 'Wukotich', 'email' => 'awukotich@peakproperties.biz']
        ];


        foreach ($managersContactInfo as $contactInfo)
        {
            $emailInfo = ['fromName'    => 'SilverIP Customer Care',
                          'fromAddress' => 'help@silverip.com',
                          'toName'      => $contactInfo['first_name'] . ' ' . $contactInfo['last_name'],
                          'toAddress'   => $contactInfo['email'],
//                          'subject'     => 'SilverIP Maintenance Window'];
//                          'subject'     => 'COMPLETED: SilverIP Network Maintenance'];
//                          'subject'     => 'EXTENDED: SilverIP Network Maintenance'];
                          'subject'     => 'Maintenance Completed!'];

//            $template = 'email.template_manager_notification';
//            $template = 'email.template_maintenance_window_complete';
//            $template = 'email.template_maintenance_window_extended';
            $template = 'email.template_maintenance_followup_notification';
            $templateData = ['manager' => $contactInfo];

            echo 'Sending to "' . $contactInfo['first_name'] . ' ' . $contactInfo['last_name'] . '" <' . $contactInfo['email'] . '>   ...   ';
            SendMail::generalEmail($emailInfo, $template, $templateData);
            echo "done\n";
        }
    }
}
