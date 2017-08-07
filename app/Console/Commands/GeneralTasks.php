<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\DataMigrationUtils;
use App\Extensions\BillingHelper;
use Storage;
use App\Models\NetworkNode;
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

                if($this->fileExists($ftp, $remoteFile) == false){
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
}
