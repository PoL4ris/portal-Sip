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

        $this->sendMassEmail();

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

        $managersContactInfo = [['first_name' => 'Peyman', 'last_name' => 'Pourkermani', 'email' => 'peyman@pourkermani.com']];

//        $managersContactInfoOLD = [
//            ['Firstname' => 'Judy', 'Lastname' => 'Pierson', 'Email' => '1400museumpark@gmail.com'],
//            ['Firstname' => 'Marcy', 'Lastname' => 'Juarez', 'Email' => 'M.Juarez@dkcondo.com'],
//            ['Firstname' => 'Leslie', 'Lastname' => 'Dyenson', 'Email' => 'L.Dyenson@DKCondo.co'],
//            ['Firstname' => 'Edison', 'Lastname' => 'Giles', 'Email' => 'egiles@lmsnet.com'],
//            ['Firstname' => 'Aislinn', 'Lastname' => 'Pulley', 'Email' => 'apulley@lmsnet.com'],
//            ['Firstname' => 'Cindy', 'Lastname' => 'Schulz', 'Email' => 'c.schulz@dkcondo.com'],
//            ['Firstname' => 'Denise', 'Lastname' => 'Savino', 'Email' => 'manager125@communityspecialists.net'],
//            ['Firstname' => 'Michael', 'Lastname' => 'Dailey', 'Email' => 'Mdailey@lmsnet.com'],
//            ['Firstname' => 'Flo', 'Lastname' => 'Roberson', 'Email' => 'mplmanager@sudlerchicago.com'],
//            ['Firstname' => 'Warren', 'Lastname' => 'Powell', 'Email' => 'wpowell@advantage-management.com'],
//            ['Firstname' => 'Kirk', 'Lastname' => 'Sullivan', 'Email' => 'k.sullivan@dkcondo.com'],
//            ['Firstname' => 'Bobby', 'Lastname' => 'Kennedy', 'Email' => 'Bobby@buildinggroup.com'],
//            ['Firstname' => 'Amy', 'Lastname' => 'Eickhoff', 'Email' => 'Aeickhoff@lmsnet.com'],
//            ['Firstname' => 'Joni', 'Lastname' => 'Hoffer', 'Email' => 'jhoffer@lmsnet.com'],
//            ['Firstname' => 'Deborah', 'Lastname' => 'Romero', 'Email' => 'D.Romero@DKCondo.com'],
//            ['Firstname' => 'Yvette', 'Lastname' => 'Lafrenere', 'Email' => 'ylafrenere@communityspecialists.net'],
//            ['Firstname' => 'April', 'Lastname' => 'Daly', 'Email' => 'A.Daly@dkcondo.com'],
//            ['Firstname' => 'Shirley', 'Lastname' => 'Feldman', 'Email' => 'S.Feldman@dkcondo.com'],
//            ['Firstname' => 'Cherie', 'Lastname' => 'Schmidt', 'Email' => '125east@museumpark.net'],
//            ['Firstname' => 'Nanci', 'Lastname' => 'Gonzalez', 'Email' => 'N.Gonzalez@dkcondo.com'],
//            ['Firstname' => 'Kathleen', 'Lastname' => 'Dormin', 'Email' => 'k.dormin@dkcondo.com'],
//            ['Firstname' => 'Sharon', 'Lastname' => 'McAndrews', 'Email' => 's.mcandrews@dkcondo.com'],
//            ['Firstname' => 'Kim', 'Lastname' => 'Pinson', 'Email' => 'k.pinson@dkcondo.com'],
//            ['Firstname' => 'Brent', 'Lastname' => 'Lehr', 'Email' => 'blehr@amli.com'],
//            ['Firstname' => 'Jermeise', 'Lastname' => 'Steele', 'Email' => 'j.steele@dkcondo.com'],
//            ['Firstname' => 'Jake', 'Lastname' => 'Larman', 'Email' => 'larman@privateholding.com'],
//            ['Firstname' => 'Zalina', 'Lastname' => 'Jones', 'Email' => 'zalina.jones@fsresidential.com'],
//            ['Firstname' => 'Holly', 'Lastname' => 'Foley', 'Email' => 'hfoley@lmsnet.com'],
//            ['Firstname' => 'Stephanie', 'Lastname' => 'Skelley', 'Email' => 'stephanie.skelley@associa.us'],
//            ['Firstname' => 'Mollie', 'Lastname' => 'Johnstone', 'Email' => 'mjohnstone@habitat.com'],
//            ['Firstname' => 'Carly', 'Lastname' => 'Sweeney', 'Email' => 'csweeney@resideliving.com'],
//            ['Firstname' => 'Gayle', 'Lastname' => 'Lang', 'Email' => 'gl@phoenixrisinggroup.com'],
//            ['Firstname' => 'Mavis', 'Lastname' => 'Mather', 'Email' => 'm.mather@dkcondo.com'],
//            ['Firstname' => 'David', 'Lastname' => 'Westveer', 'Email' => 'david@westwardmanagement.com'],
//            ['Firstname' => 'Jaime', 'Lastname' => 'Sartin', 'Email' => 'j.sartin@dkcondo.com'],
//            ['Firstname' => 'Heather', 'Lastname' => 'McHenry', 'Email' => 'heather.mchenry@related.com'],
//            ['Firstname' => 'Christina', 'Lastname' => 'Hannigan', 'Email' => 'joffreytowermgr@sudlerchicago.com'],
//            ['Firstname' => 'Jake', 'Lastname' => 'Larman', 'Email' => 'larman@privateholding.com'],
//            ['Firstname' => 'Kathy', 'Lastname' => 'Weinstein', 'Email' => 'kweinstein@lms.net'],
//            ['Firstname' => 'Jennifer', 'Lastname' => 'Bastidas', 'Email' => 'jennifer.bastidas@related.com'],
//            ['Firstname' => 'Donna', 'Lastname' => 'Ciota', 'Email' => 'donna.ciota@associa.us'],
//            ['Firstname' => 'Sharron', 'Lastname' => 'Healy', 'Email' => 'shealy@peakproperties.biz'],
//            ['Firstname' => 'Teena', 'Lastname' => 'Lorie', 'Email' => 'teena@buildinggroup.com'],
//            ['Firstname' => 'Austin', 'Lastname' => 'Arkush', 'Email' => 'AustinA@westwardmanagement.com'],
//            ['Firstname' => 'Sandy', 'Lastname' => 'Albecker', 'Email' => 'sandy@enlan.com'],
//            ['Firstname' => 'Sarah', 'Lastname' => 'Florie', 'Email' => 'S.Florie@DKCondo.com'],
//            ['Firstname' => 'Lily', 'Lastname' => 'Godow', 'Email' => 'lgodow@burnhampointechicago.com'],
//            ['Firstname' => 'Edin', 'Lastname' => 'Dzafic', 'Email' => 'manager@hermitagehuron.com'],
//            ['Firstname' => 'Jennifer', 'Lastname' => 'Taylor', 'Email' => 'jtaylor@forthgrp.com'],
//            ['Firstname' => 'Serina', 'Lastname' => 'Brancato', 'Email' => 's.brancato@dkcondo.com'],
//            ['Firstname' => 'Katharine', 'Lastname' => 'Rousseve', 'Email' => 'k.rousseve@dkcondo.com'],
//            ['Firstname' => 'Linda', 'Lastname' => 'Ivery', 'Email' => '235wvanburenmgr@sudlerchicago.com'],
//            ['Firstname' => 'Kaitlin', 'Lastname' => 'McLearen', 'Email' => '900@amli.com'],
//            ['Firstname' => 'Yvette', 'Lastname' => 'Young', 'Email' => '1700mgr@sudlerchicago.com'],
//            ['Firstname' => 'Mary', 'Lastname' => 'Wolf', 'Email' => 'm.wolf@dkcondo.com'],
//            ['Firstname' => 'Steve', 'Lastname' => 'Schantz', 'Email' => 'stevetschantz@gmail.com'],
//            ['Firstname' => 'Tricia', 'Lastname' => 'Conway', 'Email' => 'tconway@communityspecialists.net'],
//            ['Firstname' => 'Property', 'Lastname' => 'Manager', 'Email' => 'mgrlakeside@sudlerchicago.com']];


//        $managersContactInfo = [
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
//            ['first_name' => 'Nanci', 'last_name' => 'Gonzalez', 'email' => 'N.Gonzalez@dkcondo.com'],
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
//            ['first_name' => 'Amy', 'last_name' => 'Wukotich', 'email' => 'awukotich@peakproperties.biz']];


        foreach ($managersContactInfo as $contactInfo)
        {
//            dd([$contactInfo]);

            $emailInfo = ['fromName'    => 'SilverIP Customer Care',
                          'fromAddress' => 'help@silverip.com',
                          'toName'      => $contactInfo['first_name'] . ' ' . $contactInfo['last_name'],
                          'toAddress'   => $contactInfo['email'],
                          'subject'     => 'SilverIP Maintenance Window'];

            $template = 'email.template_manager_notification';
            $templateData = ['manager' => $contactInfo];

            echo 'Sending to "' . $contactInfo['first_name'] . ' ' . $contactInfo['last_name']. '" <'.$contactInfo['email'].'>   ...   ';
            SendMail::generalEmail($emailInfo, $template, $templateData);
            echo "done\n";
        }
    }
}
