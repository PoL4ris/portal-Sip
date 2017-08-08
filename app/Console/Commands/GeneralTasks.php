<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;
use App\Extensions\BillingHelper;
use Storage;
use App\Models\NetworkNode;
use App\Models\CustomerPort;
use FtpClient\FtpClient;
use FtpClient\FtpException;

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

//        $this->cleanupBadCustomerPorts();

//        $billingHelper = new BillingHelper();
//        $result = $billingHelper->processPendingAutopayInvoices();
//        $dbMigrationUtil = new DataMigrationUtils(true);
//        $dbMigrationUtil->generalDatabaseTask();
//        $this->updateMikrotikHotspotLoginFiles();

        $this->info('Done');

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

        $runQuery = function ($startingId, $recordsPerCycle)
        {
            return CustomerPort::where('id', '>', $startingId)
                ->orderBy('id', 'asc')
                ->take($recordsPerCycle)
                ->get();
        };

        $recordsPerCycle = 50;
        $startingId = 14391; // 0

        echo 'Checking '. $totalRecords .' records ... '."\n";

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
                if($customer == null){
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing a customer'."\n";
                    $badPorts ++;
                    $startingId = $customerPort->id;
                    CustomerPort::destroy($customerPort->id);
                    $recordsProcessed ++;
                    continue;
                }
                $customerAddress = $customer->address;

                $port = $customerPort->port;
                if($customer == null){
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing a port'."\n";
                    $badPorts ++;
                    $startingId = $customerPort->id;
                    CustomerPort::destroy($customerPort->id);
                    $recordsProcessed ++;
                    continue;
                }
                $portAddress = $port->address;

                if ($portAddress == null)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' is missing an address'."\n";
                    CustomerPort::destroy($customerPort->id);
                    $badPorts ++;
                } else if ($customerAddress != null && $portAddress->code != $customerAddress->code)
                {
                    echo 'CustomerPort: ' . $customerPort->id . ' does not match'."\n";
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

        echo $badPorts . ' out of ' . $recordsProcessed . ' were bad'."\n";
    }
}
