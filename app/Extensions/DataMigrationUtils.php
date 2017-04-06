<?php

namespace App\Extensions;

use DB;
use Log;
use \Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;

/**
** New database models
**/
use App\Models\AccessApp;
use App\Models\AccessAppElement;
use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\App;
use App\Models\BillingTransactionLog;
use App\Models\Building\Building;
use App\Models\Building\BuildingProperty;
use App\Models\BuildingContact;
use App\Models\BuildingProduct;
use App\Models\BuildingPropertyValue;
use App\Models\Category;
use App\Models\Contact;
use App\Models\ContactType;
use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\DataMigration;
use App\Models\DhcpLease;
use App\Models\Element;
use App\Models\Invoice;
use App\Models\InvoiceLog;
use App\Models\Neighborhood;
use App\Models\NetworkNode;
use App\Models\NetworkTab;
use App\Models\Note;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Port;
use App\Models\Product;
use App\Models\ProductProperty;
use App\Models\ProductPropertyValue;
use App\Models\Profile;
use App\Models\Reason;
use App\Models\ScheduledJob;
use App\Models\Status;
use App\Models\Term;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketNote;
use App\Models\Type;
use App\Models\User;

/**
** Legacy database models
**/
use App\Models\Legacy\AdminAccess;
use App\Models\Legacy\BillingTransactionLogOld;
use App\Models\Legacy\CustomerOld;
use App\Models\Legacy\CustomerProductOld;
use App\Models\Legacy\DataServicePort;
use App\Models\Legacy\NetworkNodeOld;
use App\Models\Legacy\NetworkTabOld;
use App\Models\Legacy\ProductOld;
use App\Models\Legacy\ProductPropertyOld;
use App\Models\Legacy\ProductPropertyValueOld;
use App\Models\Legacy\RetailRevenue;
use App\Models\Legacy\SalesActivity;
use App\Models\Legacy\SalesPropertyImage;
use App\Models\Legacy\SalesPropertyInfo;
use App\Models\Legacy\SalesPropertyReminder;
use App\Models\Legacy\ServiceLocation;
use App\Models\Legacy\ServiceLocationGroup;
use App\Models\Legacy\ServiceLocationGroupValue;
use App\Models\Legacy\ServiceLocationProduct;
use App\Models\Legacy\ServiceLocationProperty;
use App\Models\Legacy\ServiceLocationPropertyValue;
use App\Models\Legacy\SupportTicket;
use App\Models\Legacy\SupportTicketHistory;
use App\Models\Legacy\SupportTicketReason;


class DataMigrationUtils {

    //    private $testMode = true;
    //    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    private $console = false;
    private $progress = null;
    private $output = null;

    private $tableMap = array('customers'                       => ['App\Models\Legacy\CustomerOld', 'CID', 'App\Models\Customer'],
                              'serviceLocation'                 => ['App\Models\Legacy\ServiceLocation', 'LocID', 'App\Models\Building'],
                              'serviceLocationProperties'       => ['App\Models\Legacy\ServiceLocationProperty', 'PropID', 'App\Models\BuildingProperty'],
                              'serviceLocationPropertyValues'   => ['App\Models\Legacy\ServiceLocationPropertyValue', 'VID', 'App\Models\BuildingPropertyValue'],
                              'products'                        => ['App\Models\Legacy\ProductOld', 'ProdID', 'App\Models\Product'],
                              'productProperties'               => ['App\Models\Legacy\ProductPropertyOld', 'PropID', 'App\Models\ProductProperty'],
                              'productPropertyValues'           => ['App\Models\Legacy\ProductPropertyValueOld', 'VID', 'App\Models\ProductPropertyValue'],
                              'customerProducts'                => ['App\Models\Legacy\CustomerProductOld', 'CSID', 'App\Models\CustomerProduct'],
                              'serviceLocationProducts'         => ['App\Models\Legacy\ServiceLocationProduct', 'SLPID', 'App\Models\BuildingProduct'],
                              'networkNodes'                    => ['App\Models\Legacy\NetworkNodeOld', 'NodeID', 'App\Models\NetworkNode'],
                              'dataServicePorts'                => ['App\Models\Legacy\DataServicePort', 'PortID', 'App\Models\Port'],
                              'supportTicketReasons'            => ['App\Models\Legacy\SupportTicketReason', 'RID', 'App\Models\Reason'],
                              'supportTickets'                  => ['App\Models\Legacy\SupportTicket', 'TID', 'App\Models\Ticket'],
                              'supportTicketHistory'            => ['App\Models\Legacy\SupportTicketHistory', 'THID', 'App\Models\TicketHistory'],
                              'billingTransactionLog'           => ['App\Models\Legacy\BillingTransactionLogOld',  'LogID', 'App\Models\BillingTransactionLog'],
                              'networkTab'                      => ['App\Models\Legacy\NetworkTabOld',  'NID', 'App\Models\NetworkTab']);

    private $jobNames = [ 'update-from-legacy-db-job' => 'data:update-from-legacy' ];

    public function __construct($console = false) {

        $emptyFunction = function(){ };
        foreach($this->tableMap as $key => $migrationArray) {
            $this->tableMap[$key][] = $emptyFunction;
        }

        if($console == true) {
            $this->console = $console;
            $this->output = new ConsoleOutput();
        }

        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        // DB::connection()->enableQueryLog();
        //        $queries = DB::getQueryLog();
        //        $last_query = end($queries);
        //        dd($last_query);
        //        $configPasscode = config('billing.ippay.passcode');
        //        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    public function testProgressBar(){

        //        $this->startProgressBar(1);
        if($this->output != null){
            $this->output->writeln('Console output initialized');
        }

        //        return;

        $units = 50;
        //
        //        $output = new ConsoleOutput();
        //        //        $output->setFormatter(new OutputFormatter(true));
        //
        //        $this->output->writeln('Starting in 5 seconds ...');
        //        // create a new progress bar (50 units)
        //        $progress = new ProgressBar($output, $units);
        //        //        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        //        //        $progress->setFormat('table1:   %current% [%bar%] %percent:3s%%       %estimated:-6s%');
        //        $progress->setFormatDefinition('custom', ' %component%:    %current%/%max% [%bar%] %percent:3s%%       %estimated:-6s%');
        //        $progress->setFormat('custom');
        //        $progress->setMessage('test', 'component');
        //        $progress->start();
        //        sleep(5);

        if($this->startProgressBar($units, 'test') == false){
            return false;
        }

        $i = 0;
        while ($i++ < $units) {

            //            $progress->setMessage('Importing ...');
            //            $progress->setMessage($i, 'table');

            // advance the progress bar 1 unit
            //            $progress->advance();
            $this->advanceProgressBar();
            //             $this->progress->setProgress($progress);

            // you can also advance the progress bar by more than 1 unit
            // $progress->advance(3);
            usleep(500000);
        }
        $progress->finish();
        $output->writeln('');
    }

    #############################
    # Migrate functions
    #############################

    public function migrateCustomersTable(){
        $legacyTableName = 'customers';
        $this->tableMap[$legacyTableName][3] = function($legacyCustomer){
            $this->updateCustomer($legacyCustomer, new Customer);
            $this->updateAddressByCustomer($legacyCustomer, new Address);
            $this->updatePaymentMethod($legacyCustomer, new PaymentMethod);
            $this->addContactsForCustomer($legacyCustomer);
            $this->addNoteForCustomer($legacyCustomer);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateServiceLocationsTable(){
        $legacyTableName = 'serviceLocation';
        $customQueryFunction = function($legacyDataModelName, $legacyModelId, $startingId, $recordsPerCycle){
            return ServiceLocation::where($legacyModelId, '>', $startingId)
                ->where($legacyModelId, '!=', 1)
                ->orderBy($legacyModelId, 'asc')
                ->take($recordsPerCycle)
                ->get();
        };

        $this->tableMap[$legacyTableName][3] = function($legacyLocation){

            $result = $this->updateBuilding($legacyLocation, new Building);
            if($result == false) {
                return false;
            }
            $this->updateAddressByBuilding($legacyLocation, new Address);
            $this->updateBuildingPropertyValues($legacyLocation);
            return true;
        };

        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName], -1, $customQueryFunction);

        $this->updateBuildingData();
    }

    public function migrateServiceLocationPropertiesTable() {
        $legacyTableName = 'serviceLocationProperties';
        $this->tableMap[$legacyTableName][3] = function($legacyLocationProperty){
            $this->updateBuildingProperty($legacyLocationProperty, new BuildingProperty);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateAdditionalServiceLocationPropertyValues(){
        $legacyTableName = 'serviceLocationPropertyValues';
        $this->tableMap[$legacyTableName][3] = function($serviceLocationPropertyValue){
            $this->findOrCreateBuildingPropertyValue($serviceLocationPropertyValue->LocID, $serviceLocationPropertyValue->PropID, $serviceLocationPropertyValue->Value);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
        BuildingPropertyValue::where('id_buildings', 0)
            ->update(['id_buildings' => 1]);
    }

    public function migrateProductsTable(){
        $legacyTableName = 'products';
        $this->tableMap[$legacyTableName][3] = function($legacyProduct){
            $this->updateProduct($legacyProduct, new Product);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateProductPropertiesTable(){
        $legacyTableName = 'productProperties';
        $this->tableMap[$legacyTableName][3] = function($legacyProductProperty){
            $this->updateProductProperty($legacyProductProperty, new ProductProperty);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateProductPropertyValuesTable(){
        $legacyTableName = 'productPropertyValues';
        $this->tableMap[$legacyTableName][3] = function($legacyProductPropertyValue){
            $this->updateProductPropertyValue($legacyProductPropertyValue, new ProductPropertyValue);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateCustomerProductsTable(){
        $legacyTableName = 'customerProducts';
        $this->tableMap[$legacyTableName][3] = function($legacyCustomerProduct){
            $this->updateCustomerProduct($legacyCustomerProduct, new CustomerProduct);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateBuildingProductsTable() {
        $legacyTableName = 'serviceLocationProducts';
        $this->tableMap[$legacyTableName][3] = function($legacyBuildingProduct){
            $this->updateBuildingProduct($legacyBuildingProduct, new BuildingProduct);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateNetworkNodesTable(){
        $legacyTableName = 'networkNodes';
        $this->tableMap[$legacyTableName][3] = function($legacyNetworkNode){
            $this->updateNetworkNode($legacyNetworkNode, new NetworkNode);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateDataServicePortsTable(){
        $legacyTableName = 'dataServicePorts';
        $this->tableMap[$legacyTableName][3] = function($legacyPort){
            $this->updatePort($legacyPort, new Port);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateSupportTicketReasonsTable() {
        $legacyTableName = 'supportTicketReasons';
        $this->tableMap[$legacyTableName][3] = function($legacyReason){
            $this->updateReason($legacyReason, new Reason);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateSupportTicketsTable(){
        $legacyTableName = 'supportTickets';
        $this->tableMap[$legacyTableName][3] = function($legacyTicket){
            $this->updateTicket($legacyTicket, new Ticket);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateSupportTicketHistoryTable(){
        $legacyTableName = 'supportTicketHistory';
        $this->tableMap[$legacyTableName][3] = function($legacyTicketHistory){
            $this->updateTicketHistory($legacyTicketHistory, new TicketHistory);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateBillingTransactionLogsTable(){
        $legacyTableName = 'billingTransactionLog';
        $this->tableMap[$legacyTableName][3] = function($legacyTransactionLog){
            $this->updateTransactionLog($legacyTransactionLog, new BillingTransactionLog);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    public function migrateNetworkTabTable(){
        $legacyTableName = 'networkTab';
        $this->tableMap[$legacyTableName][3] = function($legacyNetworkTab){
            $this->updateNetworkTab($legacyNetworkTab, new NetworkTab);
            return true;
        };
        $this->migrateTable($legacyTableName, $this->tableMap[$legacyTableName]);
    }

    #################################
    # Update/Maintenance functions
    #################################

    public function updateFromCustomersTable(){
        $legacyTableName = 'customers';
        $updateFunction = function($legacyCustomer){
            Log::info('Updating customer: '.$legacyCustomer->CID);
            $this->findOrCreateCustomer($legacyCustomer);
            $this->findOrCreateAddressByCustomer($legacyCustomer);
            $this->findOrCreatePaymentMethod($legacyCustomer);
            $this->updateContactByCustomer($legacyCustomer);
            $this->updateNotesByCustomer($legacyCustomer);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromServiceLocationsTable(){
        $legacyTableName = 'serviceLocation';
        $updateFunction = function($legacyLocation){
            $result = $this->findOrCreateBuilding($legacyLocation);
            if($result == false) {
                return false;
            }
            $this->findOrCreateAddressByBuilding($legacyLocation);
            $this->updateBuildingPropertyValues($legacyLocation);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromServiceLocationPropertiesTable() {
        $legacyTableName = 'serviceLocationProperties';
        $updateFunction = function($legacyLocationProperty){
            $this->findOrCreateBuildingProperty($legacyLocationProperty);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromProductsTable(){
        $legacyTableName = 'products';
        $updateFunction = function($legacyProduct){
            $this->findOrCreateProduct($legacyProduct);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromProductPropertiesTable(){
        $legacyTableName = 'productProperties';
        $updateFunction = function($legacyProductProperty){
            $this->findOrCreateProductProperty($legacyProductProperty);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromProductPropertyValuesTable(){
        $legacyTableName = 'productPropertyValues';
        $updateFunction = function($legacyProductPropertyValue){
            $this->findOrCreateBuildingPropertyValue($legacyProductPropertyValue->LocID, $legacyProductPropertyValue->PropID, $legacyProductPropertyValue->Value);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromCustomerProductsTable(){
        $legacyTableName = 'customerProducts';
        $updateFunction = function($legacyCustomerProduct){
            $this->findOrCreateCustomerProduct($legacyCustomerProduct);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromBuildingProductsTable() {
        $legacyTableName = 'serviceLocationProducts';
        $updateFunction = function($legacyBuildingProduct){
            $this->findOrCreateBuildingProduct($legacyBuildingProduct);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromNetworkNodesTable(){
        $legacyTableName = 'networkNodes';
        $updateFunction = function($legacyNetworkNode){
            $this->findOrCreateNetworkNode($legacyNetworkNode);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromDataServicePortsTable(){
        $legacyTableName = 'dataServicePorts';
        $updateFunction = function($legacyPort){
            $this->findOrCreatePort($legacyPort);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromSupportTicketReasonsTable() {
        $legacyTableName = 'supportTicketReasons';
        $updateFunction = function($legacyReason){
            $this->findOrCreateReason($legacyReason);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromSupportTicketsTable(){
        $legacyTableName = 'supportTickets';
        $updateFunction = function($legacyTicket){
            $this->findOrCreateTicket($legacyTicket);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromSupportTicketHistoryTable(){
        $legacyTableName = 'supportTicketHistory';
        $updateFunction = function($legacyTicketHistory){
            $this->findOrCreateTicketHistory($legacyTicketHistory);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromBillingTransactionLogsTable(){
        $legacyTableName = 'billingTransactionLog';
        $updateFunction = function($legacyTransactionLog){
            $this->findOrCreateTransactionLog($legacyTransactionLog);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    public function updateFromNetworkTabTable(){
        $legacyTableName = 'networkTab';
        $updateFunction = function($legacyNetworkTab){
            $this->findOrCreateNetworkTab($legacyNetworkTab);
            return true;
        };
        $this->updateTable($legacyTableName, $this->tableMap[$legacyTableName], $updateFunction, $updateFunction);
    }

    #############################
    # Seed functions
    #############################

    public function seedDataMigrationsTable(){

        if($this->output != null){
            $this->output->writeln('<info> Seeding data_migrations table</info>');
        }

        foreach($this->tableMap as $tableName => $tableModelMap){
            DataMigration::firstOrCreate(['table_name' => $tableName]);
        }

        //        DataMigration::firstOrCreate(['table_name' => 'categories']);
        //        DataMigration::firstOrCreate(['table_name' => 'contacts']);

    }

    public function seedCategoriesTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding categories table</info>');
        }
        Category::firstOrCreate(['id' => 1, 'name' => 'INT']);
        Category::firstOrCreate(['id' => 2, 'name' => 'PH']);
        Category::firstOrCreate(['id' => 3, 'name' => 'MISC']);
        Category::firstOrCreate(['id' => 4, 'name' => 'TV']);
    }

    public function seedAppsTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding apps table</info>');
        }

        App::firstOrCreate(['id' => 1, 'id_apps' => 0, 'name' => 'Support', 'icon' => 'fa-wrench', 'url' => 'support']);
        App::firstOrCreate(['id' => 2, 'id_apps' => 0, 'name' => 'Customers', 'icon' => 'fa-user ', 'url' => 'customers']);
        App::firstOrCreate(['id' => 3, 'id_apps' => 0, 'name' => 'Building', 'icon' => 'fa-building-o', 'url' => 'buildings']);
        App::firstOrCreate(['id' => 4, 'id_apps' => 0, 'name' => 'Network', 'icon' => 'fa-signal', 'url' => 'network']);

        // FOR FUTURE USE
        //        App::firstOrCreate(['id' => 1, 'id_apps' => 0, 'name' => 'Home', 'icon' => 'icon-home', 'url' => '#/']);
        //        App::firstOrCreate(['id' => 5, 'id_apps' => 0, 'name' => 'Admin', 'icon' => 'icon-ghost', 'url' => '#/admin']);
        //        App::firstOrCreate(['id' => 6, 'id_apps' => 0, 'name' => 'Clients', 'icon' => 'fa fa-money', 'url' => '#/clients']);
        //        App::firstOrCreate(['id' => 8, 'id_apps' => 0, 'name' => 'Support Calendar', 'icon' => 'icon-calendar', 'url' => '#/calendar']);
    }

    public function seedUsersTable() {

        if($this->output != null){
            $this->output->writeln('<info> Seeding users table</info>');
        }

        User::firstOrCreate(['id' => 1, 'first_name' => 'Pablo', 'last_name' => 'Laris', 'email' => 'pablo@silverip.com', 'password' => '$2y$10$u.sqr/WkAQaJL7FCCQVmGue8efy3wAdF1E/OKGr5XQgxS8vDPCJ.2', 'remember_token' => 'Jonk6hBM0kmkQipY2WTTmrfFN2YZrZDQzzj37GqwTObdij5LOOUnjFeLF8Qb', 'social_token' => 'ya29.Gl0WBAIa60ypaAaSdSo-rfT08I_1X4poFF9TM3MgF2Bs3E83GYaYoTiTP2ljuN_n4dP6sf6ZDhNBzYp9zo2I-Vl7D-VWkmk', 'social_access' => 1, 'avatar' => 'https://lh4.googleusercontent.com/-MY1G9QEJ8M4/AAAAAAAAAAI/AAAAAAAAABM/wLAg7FpkX5E/photo.jpg?sz=50', 'alias' => 'PL', 'id_status' => 3, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 2, 'first_name' => 'Farzad', 'last_name' => 'Farzad', 'email' => 'farzad@silverip.com', 'password' => '$2a$08$UdmCQyKxZlWEfbl94yB4e.M4zS9lg88Osea8lQ8Ay0NQ9KxD05Rkq', 'remember_token' => 'DN5OipGB4aH2dU233R3hAKnCQ1qoeqSN1VnQHnxJPHyKLnvg5ldpYdPU8Qov', 'social_token' => '', 'social_access' => 1, 'avatar' => 'https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50', 'alias' => 'FM', 'id_status' => 3, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 3, 'first_name' => 'Peyman', 'last_name' => 'Pourkermani', 'email' => 'peyman@silverip.com', 'password' => '$2y$10$12q8f3L/WXUiwRpQdYjOX.7HzTx/7akNIfqItbJncfYCr5z5egEjO', 'remember_token' => 'MA1xsnRUhbTRteuxmziO9Pe1eBp74nzBifwRhC2cmkSyu45ZA7TvYOKK2Khq', 'social_token' => 'ya29.CjNrA0b1vBMPk49P_gZ9TOSlV7ajVYFVixiKE6py_eBOcgL7isD2bFuIrxzO-CiSlre8r7g', 'social_access' => 1, 'avatar' => 'https://lh6.googleusercontent.com/-2JxSuRyHszI/AAAAAAAAAAI/AAAAAAAAABA/FXimdQSdkwQ/photo.jpg?sz=50', 'alias' => 'PP', 'id_status' => 3, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 4, 'first_name' => 'admin', 'last_name' => 'admin', 'email' => 'admin@admin.com', 'password' => '$2a$06$lRhl6zzwSxCKUrGPKAWM0OL6MgYECwjB6Hv02zPOGsGThmmKjINl2', 'remember_token' => '', 'social_token' => '', 'social_access' => 1, 'avatar' => '', 'alias' => 'A', 'id_status' => 3, 'id_profiles' => 1]);
    }

    public function seedStatusTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding status table</info>');
        }
        //        Status::firstOrCreate(['id' => 1, 'name' => 'DISABLED']);
        //        Status::firstOrCreate(['id' => 2, 'name' => 'ACTIVE']);
        Status::firstOrCreate(['id' => 1, 'name' => 'active']);
        Status::firstOrCreate(['id' => 2, 'name' => 'disabled']);
        Status::firstOrCreate(['id' => 3, 'name' => 'new']);
        Status::firstOrCreate(['id' => 4, 'name' => 'decommissioned']);
    }

    public function seedTypesTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding types table</info>');
        }
        Type::firstOrCreate(['id' => 1, 'name' => 'Internet']);
        Type::firstOrCreate(['id' => 2, 'name' => 'Phone']);
        Type::firstOrCreate(['id' => 3, 'name' => 'Phone-Option']);
        Type::firstOrCreate(['id' => 4, 'name' => 'Customer Router']);
        Type::firstOrCreate(['id' => 5, 'name' => 'Ethernet Jack']);
        Type::firstOrCreate(['id' => 6, 'name' => 'Other']);
        Type::firstOrCreate(['id' => 7, 'name' => 'Router']);
        Type::firstOrCreate(['id' => 8, 'name' => 'Switch']);
        Type::firstOrCreate(['id' => 9, 'name' => 'Credit Card']);
        Type::firstOrCreate(['id' => 10, 'name' => 'Debit Card']);
        Type::firstOrCreate(['id' => 11, 'name' => 'Cable Run']);
        Type::firstOrCreate(['id' => 12, 'name' => 'Activation Fee']);
        Type::firstOrCreate(['id' => 13, 'name' => 'Autopay']);
        Type::firstOrCreate(['id' => 14, 'name' => 'Manual Pay']);
    }

    public function seedContactTypesTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding contacts table</info>');
        }
        ContactType::firstOrCreate(['id' => 1, 'name' => 'Mobile Phone']);
        ContactType::firstOrCreate(['id' => 2, 'name' => 'Home Phone']);
        ContactType::firstOrCreate(['id' => 3, 'name' => 'Fax']);
        ContactType::firstOrCreate(['id' => 4, 'name' => 'Work Phone']);
        ContactType::firstOrCreate(['id' => 5, 'name' => 'Email']);
    }

    public function seedBuildingPropertiesTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding building_properties table</info>');
        }
        BuildingProperty::firstOrCreate(['id' => '1', 'name' => 'Type', 'description' => 'Type']);
        BuildingProperty::firstOrCreate(['id' => '2', 'name' => 'Units', 'description' => 'Units']);
        BuildingProperty::firstOrCreate(['id' => '3', 'name' => 'Service Type', 'description' => 'Service type']);
        BuildingProperty::firstOrCreate(['id' => '4', 'name' => 'Contract Expires', 'description' => 'Contract expires on']);
        BuildingProperty::firstOrCreate(['id' => '5', 'name' => 'Mgmt Company', 'description' => 'Management company']);
        BuildingProperty::firstOrCreate(['id' => '6', 'name' => 'Ethernet', 'description' => 'Ethernet service available?']);
        BuildingProperty::firstOrCreate(['id' => '7', 'name' => 'Wireless', 'description' => 'Common are wifi details']);
        BuildingProperty::firstOrCreate(['id' => '8', 'name' => 'Speeds', 'description' => 'Available Internet speeds and packages']);
        BuildingProperty::firstOrCreate(['id' => '9', 'name' => 'Billing', 'description' => 'Billing information for available packages']);
        BuildingProperty::firstOrCreate(['id' => '10', 'name' => 'Email Service', 'description' => 'Do we provide email service']);
        BuildingProperty::firstOrCreate(['id' => '11', 'name' => 'IP', 'description' => 'IP']);
        BuildingProperty::firstOrCreate(['id' => '12', 'name' => 'DNS', 'description' => 'DNS']);
        BuildingProperty::firstOrCreate(['id' => '13', 'name' => 'Gateway', 'description' => 'Gateway']);
        BuildingProperty::firstOrCreate(['id' => '14', 'name' => 'How To Connect', 'description' => 'Instructions on how to connect to the service']);
        BuildingProperty::firstOrCreate(['id' => '15', 'name' => 'Description', 'description' => 'Description']);
        BuildingProperty::firstOrCreate(['id' => '16', 'name' => 'Support Number', 'description' => 'Support number']);
        BuildingProperty::firstOrCreate(['id' => '17', 'name' => 'Image', 'description' => 'Building image']);
    }

    public function seedNeighborhoodTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding neighborhoods table</info>');
        }
        Neighborhood::firstOrCreate(['id' => 1, 'name' => 'Gold Coast']);
        Neighborhood::firstOrCreate(['id' => 2, 'name' => 'River North']);
        Neighborhood::firstOrCreate(['id' => 3, 'name' => 'South Loop']);
        Neighborhood::firstOrCreate(['id' => 4, 'name' => 'University Village']);
        Neighborhood::firstOrCreate(['id' => 5, 'name' => 'West Loop']);
        Neighborhood::firstOrCreate(['id' => 6, 'name' => 'Loop']);
        Neighborhood::firstOrCreate(['id' => 7, 'name' => 'Lakeshore East']);
        Neighborhood::firstOrCreate(['id' => 8, 'name' => 'Streeterville']);
        Neighborhood::firstOrCreate(['id' => 9, 'name' => 'Lincoln Park']);
        Neighborhood::firstOrCreate(['id' => 10, 'name' => 'Fulton River District']);
        Neighborhood::firstOrCreate(['id' => 11, 'name' => 'South Side']);
        Neighborhood::firstOrCreate(['id' => 12, 'name' => 'Lake View']);
        Neighborhood::firstOrCreate(['id' => 13, 'name' => 'Kingsbury Park']);
        Neighborhood::firstOrCreate(['id' => 14, 'name' => 'Edgewater']);
        Neighborhood::firstOrCreate(['id' => 15, 'name' => 'East Hyde Park']);
        Neighborhood::firstOrCreate(['id' => 16, 'name' => 'McKinley Park']);
        Neighborhood::firstOrCreate(['id' => 17, 'name' => 'Near North Side']);
        Neighborhood::firstOrCreate(['id' => 18, 'name' => 'West Town']);
    }

    protected function updateBuildingData() {

        if($this->output != null){
            $this->output->writeln('<info> Updating building table data.</info>');
        }
        Building::where('code', '1400MP')->update(['floors' => 34, 'id_neighborhoods' => 3]);
        Building::where('code', 'UC2')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', 'UC3')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', 'UC1')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', '111M')->update(['floors' => 32, 'id_neighborhoods' => 1]);
        Building::where('code', 'UC5')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', '1235P')->update(['floors' => 38, 'id_neighborhoods' => 3]);
        Building::where('code', '125J')->update(['floors' => 32, 'id_neighborhoods' => 5]);
        Building::where('code', '222C')->update(['floors' => 57, 'id_neighborhoods' => 7]);
        Building::where('code', '340R')->update(['floors' => 62, 'id_neighborhoods' => 7]);
        Building::where('code', '41E8')->update(['floors' => 33, 'id_neighborhoods' => 6]);
        Building::where('code', '565Q')->update(['floors' => 18, 'id_neighborhoods' => 5]);
        Building::where('code', '60M')->update(['floors' => 72, 'id_neighborhoods' => 6]);
        Building::where('code', '659R')->update(['floors' => 17, 'id_neighborhoods' => 5]);
        Building::where('code', '701W')->update(['floors' => 34, 'id_neighborhoods' => 6]);
        Building::where('code', '737W')->update(['floors' => 38, 'id_neighborhoods' => 5]);
        Building::where('code', '800C')->update(['floors' => 11, 'id_neighborhoods' => 6]);
        Building::where('code', '901M')->update(['floors' => 10, 'id_neighborhoods' => 5]);
        Building::where('code', '1250I')->update(['floors' => 13, 'id_neighborhoods' => 3]);
        Building::where('code', '4800C')->update(['floors' => 27, 'id_neighborhoods' => 15]);
        Building::where('code', '2323P')->update(['floors' => 4, 'id_neighborhoods' => 16]);
        Building::where('code', '616F')->update(['floors' => 7, 'id_neighborhoods' => 5]);
        Building::where('code', '1910I')->update(['floors' => 7, 'id_neighborhoods' => 3]);
        Building::where('code', 'UC4')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', 'UC6')->update(['floors' => 4, 'id_neighborhoods' => 4]);
        Building::where('code', '1550B')->update(['floors' => 12, 'id_neighborhoods' => 4]);
        Building::where('code', '657F')->update(['floors' => 7, 'id_neighborhoods' => 5]);
        Building::where('code', '65M')->update(['floors' => 48, 'id_neighborhoods' => 6]);
        Building::where('code', '1901C')->update(['floors' => 29, 'id_neighborhoods' => 3]);
        Building::where('code', '212C')->update(['floors' => 13, 'id_neighborhoods' => 3]);
        Building::where('code', '1335P')->update(['floors' => 20, 'id_neighborhoods' => 3]);
        Building::where('code', '125E13')->update(['floors' => 14, 'id_neighborhoods' => 3]);
        Building::where('code', '1300S')->update(['floors' => 11, 'id_neighborhoods' => 1]);
        Building::where('code', '4000B')->update(['floors' => 1, 'id_neighborhoods' => 16]);
        Building::where('code', '30O')->update(['floors' => 24, 'id_neighborhoods' => 1]);
        Building::where('code', '57D')->update(['floors' => 41, 'id_neighborhoods' => 8]);
        Building::where('code', '130C')->update(['floors' => 10, 'id_neighborhoods' => 6]);
        Building::where('code', '711D')->update(['floors' => 10, 'id_neighborhoods' => 3]);
        Building::where('code', '850C')->update(['floors' => 11, 'id_neighborhoods' => 3]);
        Building::where('code', '111P')->update(['floors' => 11, 'id_neighborhoods' => 3]);
        Building::where('code', '333D')->update(['floors' => 7, 'id_neighborhoods' => 6]);
        Building::where('code', '845S')->update(['floors' => 34, 'id_neighborhoods' => 17]);
        Building::where('code', '240I')->update(['floors' => 31, 'id_neighborhoods' => 8]);
        Building::where('code', '14P')->update(['floors' => 8, 'id_neighborhoods' => 18]);
        Building::where('code', '909W')->update(['floors' => 10, 'id_neighborhoods' => 18]);
        Building::where('code', '1224V')->update(['floors' => 8, 'id_neighborhoods' => 18]);
        Building::where('code', '1524S')->update(['floors' => 8, 'id_neighborhoods' => 4]);
        Building::where('code', '1201P')->update(['floors' => 53, 'id_neighborhoods' => 3]);
        Building::where('code', '1525S')->update(['floors' => 8, 'id_neighborhoods' => 4]);
        Building::where('code', '8R')->update(['floors' => 33, 'id_neighborhoods' => 6]);
        Building::where('code', '1901CC')->update(['floors' => 1, 'id_neighborhoods' => 3]);
        Building::where('code', '2000M')->update(['floors' => 3, 'id_neighborhoods' => 3]);
        Building::where('code', '1600I')->update(['floors' => 19, 'id_neighborhoods' => 3]);
        Building::where('code', '730C')->update(['floors' => 28, 'id_neighborhoods' => 3]);
        Building::where('code', '610M')->update(['floors' => 5, 'id_neighborhoods' => 3]);
        Building::where('code', '200G')->update(['floors' => 27, 'id_neighborhoods' => 2]);
        Building::where('code', '345C')->update(['floors' => 7, 'id_neighborhoods' => 6]);
        Building::where('code', '1635B')->update(['floors' => 7, 'id_neighborhoods' => 12]);
        Building::where('code', '845F')->update(['floors' => 3, 'id_neighborhoods' => 18]);
        Building::where('code', '77W')->update(['floors' => 1, 'id_neighborhoods' => 8]);
        Building::where('code', '159W')->update(['floors' => 33, 'id_neighborhoods' => 8]);
        Building::where('code', '213I')->update(['floors' => 4, 'id_neighborhoods' => 17]);
        Building::where('code', '70H')->update(['floors' => 26, 'id_neighborhoods' => 2]);
        Building::where('code', '1339D')->update(['floors' => 16, 'id_neighborhoods' => 1]);
        Building::where('code', '360R')->update(['floors' => 40, 'id_neighborhoods' => 6]);
        Building::where('code', '235V')->update(['floors' => 46, 'id_neighborhoods' => 6]);
        Building::where('code', '501C')->update(['floors' => 34, 'id_neighborhoods' => 5]);
        Building::where('code', '900C')->update(['floors' => 24, 'id_neighborhoods' => 3]);
        Building::where('code', '1700E56')->update(['floors' => 39, 'id_neighborhoods' => 15]);
        Building::where('code', '1623L')->update(['floors' => 2, 'id_neighborhoods' => 3]);
        Building::where('code', '1046K')->update(['floors' => 4, 'id_neighborhoods' => 10]);
        Building::where('code', 'CHASE')->update(['floors' => 58, 'id_neighborhoods' => 6]);
        Building::where('code', '400C')->update(['floors' => 7, 'id_neighborhoods' => 5]);
        Building::where('code', '400X')->update(['floors' => 1, 'id_neighborhoods' => 5]);
        Building::where('code', '270P')->update(['floors' => 16, 'id_neighborhoods' => 17]);
        Building::where('code', '2037C')->update(['floors' => 4, 'id_neighborhoods' => 5]);
        Building::where('code', '308E')->update(['floors' => 7, 'id_neighborhoods' => 2]);
        Building::where('code', '505M')->update(['floors' => 49, 'id_neighborhoods' => 8]);
        Building::where('code', '71H')->update(['floors' => 50, 'id_neighborhoods' => 2]);
        Building::where('code', '770H')->update(['floors' => 6, 'id_neighborhoods' => 5]);
        Building::where('code', '1211P')->update(['floors' => 62, 'id_neighborhoods' => 3]);
        Building::where('code', '1331P')->update(['floors' => 2, 'id_neighborhoods' => 3]);
    }

    public function truncateAllTables() {


        $tables = ['users', 'apps', 'customers', 'buildings', 'building_properties',
                   'building_property_values', 'products', 'product_properties',
                   'product_property_values', 'customer_products', 'building_products',
                   'network_nodes', 'network_tabs', 'notes', 'ports', 'reasons', 'tickets', 'ticket_history',
                   'billing_transaction_logs', 'data_migrations', 'payment_methods',
                   'address', 'contacts', 'contact_types', 'categories', 'status',
                   'types', 'neighborhoods'];

        $this->startProgressBar(count($tables), 'truncating tables');
        foreach($tables as $table) {
            DB::table($table)->truncate();
            $this->advanceProgressBar();
        }
        $this->stopProgressBar();
    }

    #############################
    # Supporting functions
    #############################

    protected function migrateTable($legacyTableName, $migrationDataMap, $startingId = -1, $customQueryFuction = null){

        $legacyDataModelName = $migrationDataMap[0];
        $legacyModelId = $migrationDataMap[1];
        $newDataModelName = $migrationDataMap[2];
        $customFunc = $migrationDataMap[3];

        $recordsPerCycle = 50; //$this->getJobProperty('lease-request-job', 'records_per_cycle');

        $totalLegacyRecords = $legacyDataModelName::count();
        $this->startProgressBar($totalLegacyRecords, $legacyTableName);

        // Get the data migration record from the database
        $dataMigration = DataMigration::where('table_name', $legacyTableName)->first();
        if($dataMigration == null){
            $this->progressBarError('<fg=magenta>Could not find '.$legacyTableName.' in the data_migrations table. Ignoring.</>');
            $this->stopProgressBar();
            Log::info('Could not find '.$legacyTableName.' in the data_migrations table. Ignoring.');
            return false;
        }

        // Check to see if we can migrate or if it was already done
        if($dataMigration->status == 1){

            $this->progressBarError('<fg=magenta>already migrated</>');
            $this->stopProgressBar();
            Log::info('Table '.$legacyTableName.' has already been migrated. Ignoring.');
            return false;
        }

        // Reset the records processed count
        $dataMigration->records_processed = 0;
        // Set the total record count
        $dataMigration->total_records = $totalLegacyRecords;

        $runQuery = function($legacyDataModelName, $legacyModelId, $startingId, $recordsPerCycle){
            return $legacyDataModelName::where($legacyModelId, '>', $startingId)
                ->orderBy($legacyModelId, 'asc')
                ->take($recordsPerCycle)
                ->get();
        };

        // If the caller has specified a custom query function then use that instead
        if($customQueryFuction != null){
            $runQuery = $customQueryFuction;
        }

        while (true) {

            $legacyRecords = $runQuery($legacyDataModelName, $legacyModelId, $startingId, $recordsPerCycle);

            if($legacyRecords->count() == 0){
                break;
            }

            foreach ($legacyRecords as $legacyRecord) {

                // Call the custom function to process the migration from legacy to new
                $result = $customFunc($legacyRecord);

                if($result == false){
                    Log::info('Call to customFunc() returned false. Skipping record.');
                    $startingId = $legacyRecord->$legacyModelId;
                    continue;
                }

                // Do some accounting
                $startingId = $legacyRecord->$legacyModelId;
                $dataMigration->records_processed++;
                $dataMigration->max_processed_id = ($legacyRecord->$legacyModelId > $dataMigration->max_processed_id) ? $legacyRecord->$legacyModelId : $dataMigration->max_processed_id;

                if($dataMigration->max_created_at == null){
                    $dataMigration->max_created_at = $legacyRecord->created_at;
                } else {
                    $dataMigration->max_created_at = $this->maxMysqlTimestamp($dataMigration->max_created_at, $legacyRecord->created_at);
                }

                if($dataMigration->max_updated_at == null){
                    $dataMigration->max_updated_at = $legacyRecord->updated_at;
                } else {
                    $dataMigration->max_updated_at = $this->maxMysqlTimestamp($dataMigration->max_updated_at, $legacyRecord->updated_at);
                }
            }

            // Update the progress bar
            $this->advanceProgressBar(0, $dataMigration->records_processed);
            usleep(500000);
        }
        // mark the data migration status as complete and save it
        $dataMigration->status = 1;
        $dataMigration->save();
        $this->stopProgressBar();
        Log::info('Migrated '.$dataMigration->records_processed.' records');
        return true;
    }

    protected function updateTable($legacyTableName, $migrationDataMap, $updateFunction, $createFunction){

        $legacyDataModelName = $migrationDataMap[0];
        $legacyModelId = $migrationDataMap[1];
        $recordsPerCycle = 50; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $totalLegacyRecords = $legacyDataModelName::count();

        // Get the data migration record from the database
        $dataMigration = DataMigration::where('table_name', $legacyTableName)->first();
        if($dataMigration == null){
            $this->progressBarError('<fg=magenta>Could not find '.$legacyTableName.' in the data_migrations table. Ignoring.</>');
            $this->stopProgressBar();
            Log::info('Could not find '.$legacyTableName.' in the data_migrations table. Ignoring.');
            return false;
        }

        // Check to see if we can migrate or if it was already done
        if($dataMigration->status == 0){
            $this->progressBarError('<fg=magenta>need to migrate first then call update</>');
            $this->stopProgressBar();
            Log::info('Table '.$legacyTableName.' need to migrate first then call update. Ignoring.');
            return false;
        }

        $lastUpdateTimestamp = $dataMigration->max_updated_at;
        $updateQueryBuilder = function($legacyDataModelName, $lastUpdateTimestamp, $legacyModelId, $startingId = -1){
            if($lastUpdateTimestamp == null){
                return $legacyDataModelName::where('updated_at', '!=', $lastUpdateTimestamp);
            }
            return $legacyDataModelName::where('updated_at', '>', $lastUpdateTimestamp)
                ->where($legacyModelId, '>', $startingId);
        };
        $totalUpdateRecords = $updateQueryBuilder($legacyDataModelName, $lastUpdateTimestamp, $legacyModelId)->count();

        $lastCreateTimestamp = $dataMigration->max_created_at;
        $createQueryBuilder = function($legacyDataModelName, $lastCreateTimestamp, $legacyModelId, $startingId = -1){
            if($lastCreateTimestamp == null){
                return $legacyDataModelName::where('created_at', '!=', $lastCreateTimestamp);
            }
            return $legacyDataModelName::where('created_at', '>', $lastCreateTimestamp)
                ->where($legacyModelId, '>', $startingId);
        };
        $totalCreateRecords = $createQueryBuilder($legacyDataModelName, $lastCreateTimestamp, $legacyModelId)->count();

        $updateCount = 0;
        $createCount = 0;
        $startingId = -1;
        if($totalUpdateRecords > 0){
            $this->startProgressBar($totalUpdateRecords, $legacyTableName.' updating');
            while (true) {

                $legacyRecords = $updateQueryBuilder($legacyDataModelName, $lastUpdateTimestamp, $legacyModelId, $startingId)
                    ->orderBy($legacyModelId, 'asc')
                    ->take($recordsPerCycle)
                    ->get();

                if($legacyRecords->count() == 0){
                    break;
                }

                foreach ($legacyRecords as $legacyRecord) {

                    // Call the custom function to process the migration from legacy to new
                    $result = $updateFunction($legacyRecord);

                    if($result == false){
                        Log::info('Call to customFunc() returned false. Skipping record.');
                        $startingId = $legacyRecord->$legacyModelId;
                        continue;
                    }

                    // Do some accounting
                    $startingId = $legacyRecord->$legacyModelId;
                    if($dataMigration->max_updated_at == null){
                        $dataMigration->max_updated_at = $legacyRecord->updated_at;
                    } else {
                        $dataMigration->max_updated_at = $this->maxMysqlTimestamp($dataMigration->max_updated_at, $legacyRecord->updated_at);
                    }
                    $updateCount++;
                }

                // Update the progress bar
                $this->advanceProgressBar($updateCount);
                usleep(500000);
            }
            $this->stopProgressBar();
        }

        $startingId = -1;
        if($totalCreateRecords > 0){
            $this->startProgressBar($totalCreateRecords, $legacyTableName.' adding');
            while (true) {

                $legacyRecords = $createQueryBuilder($legacyDataModelName, $lastCreateTimestamp, $legacyModelId, $startingId)
                    ->orderBy($legacyModelId, 'asc')
                    ->take($recordsPerCycle)
                    ->get();

                if($legacyRecords->count() == 0){
                    break;
                }

                foreach ($legacyRecords as $legacyRecord) {

                    // Call the custom function to process the migration from legacy to new
                    $result = $createFunction($legacyRecord);

                    if($result == false){
                        Log::info('Call to customFunc() returned false. Skipping record.');
                        $startingId = $legacyRecord->$legacyModelId;
                        continue;
                    }

                    // Do some accounting
                    $startingId = $legacyRecord->$legacyModelId;
                    $dataMigration->records_processed++;
                    $dataMigration->max_processed_id = ($legacyRecord->$legacyModelId > $dataMigration->max_processed_id) ? $legacyRecord->$legacyModelId : $dataMigration->max_processed_id;

                    if($dataMigration->max_created_at == null){
                        $dataMigration->max_created_at = $legacyRecord->created_at;
                    } else {
                        $dataMigration->max_created_at = $this->maxMysqlTimestamp($dataMigration->max_created_at, $legacyRecord->created_at);
                    }
                    $createCount++;
                }

                // Update the progress bar
                $this->advanceProgressBar($createCount);
                usleep(500000);
            }
            $this->stopProgressBar();
        }

        // mark the data migration status as complete and save it
        $dataMigration->total_records = $totalLegacyRecords;
        $dataMigration->save();
        Log::info('Updated '.$updateCount.' and added '.$createCount.' '.$legacyTableName.' records');
        if($this->output != null){
            $this->output->writeln('<info> Updated '.$updateCount.' and added '.$createCount.' '.$legacyTableName.' records</info>');
        }
        return true;
    }

    public function updateAllDataFromLegacyDatabaseJob(){
        $jobName = 'update-from-legacy-db-job';
        if($this->isJobEnabled($jobName) == false){
            Log::notice('Job: '.$this->jobNames[$jobName].' is disabled');
            return false;
        }

        $this->setJobStatus($jobName, 'running');
        Log::notice('Job: '.$this->jobNames[$jobName].' Looking for records to update ...');
        while(true){
            $this->updateAllDataFromLegacyDatabase();
            sleep(15);
            if($this->isJobEnabled($jobName) == false){
                Log::notice('Job: '.$this->jobNames[$jobName].' has been disabled');
                break;
            }
            $this->updateJobTimestamp($jobName);
        }
        Log::notice('Job: '.$this->jobNames[$jobName].' done updating data from legacy database.');
        $this->setJobStatus($jobName, 'stopped');
    }

    public function updateAllDataFromLegacyDatabase() {
        $this->updateFromCustomersTable();
        $this->updateFromServiceLocationsTable();
        $this->updateFromServiceLocationPropertiesTable();
        $this->updateFromProductsTable();
        $this->updateFromProductPropertiesTable();
        $this->updateFromProductPropertyValuesTable();
        $this->updateFromCustomerProductsTable();
        $this->updateFromBuildingProductsTable();
        $this->updateFromNetworkNodesTable();
        $this->updateFromDataServicePortsTable();
        $this->updateFromSupportTicketReasonsTable();
        $this->updateFromSupportTicketsTable();
        $this->updateFromSupportTicketHistoryTable();
        $this->updateFromBillingTransactionLogsTable();
        $this->updateFromNetworkTabTable();
    }

    protected function maxMysqlTimestamp($timestamp1, $timestamp2, $timezone1 = 'America/Chicago', $timezone2 = 'America/Chicago'){
        if($timestamp1 == null){
            return $timestamp2;
        }

        if($timestamp2 == null){
            return $timestamp1;
        }

        $carbonTimestamp1 = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1, 'America/Chicago');
        $carbonTimestamp2 = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp2, 'America/Chicago');
        $unixTimestamp1 = $carbonTimestamp1->timestamp;
        $unixTimestamp2 = $carbonTimestamp2->timestamp;
        return (max($unixTimestamp1, $unixTimestamp2) == $unixTimestamp1) ? $carbonTimestamp1->format('Y-m-d H:i:s') : $carbonTimestamp2->format('Y-m-d H:i:s');
    }

    protected function copyTimestamps($legacyRecord, $newRecord){
        $newRecord->created_at = ($legacyRecord->created_at == '0000-00-00 00:00:00') ? null : $legacyRecord->created_at;
        $newRecord->updated_at = ($legacyRecord->updated_at == '0000-00-00 00:00:00') ? null : $legacyRecord->updated_at;
        if($legacyRecord->created_at == null && $legacyRecord->updated_at != null){
            $newRecord->created_at = $newRecord->updated_at;
        }
        return $newRecord;
    }

    protected function startProgressBar($units = null, $component = ''){

        if($this->console == false){ return; }

        if($units == null || $units < 1) {
            $this->output->writeln('<error>Progress bar units not set</error>');
            return false;
        }

        $this->progress = new ProgressBar($this->output, $units);
        $this->progress->setFormatDefinition('custom', ' %component%:    %current%/%max% [%bar%] %percent:3s%%       %estimated:-6s%');
        $this->progress->setFormat('custom');
        $this->progress->setMessage($component, 'component');
        $this->progress->start();
        return true;
        //        $output->setFormatter(new OutputFormatter(true));
    }

    protected function advanceProgressBar($count = 1, $progress = null){

        if($count > 0) {
            $this->progress->advance($count);
            return;
        }

        if($this->progress == null) { return; }
        $this->progress->setProgress($progress);
    }

    protected function progressBarError($errorMessage) {

        if($this->progress == null) { return; }
        $this->progress->setFormatDefinition('custom', ' %table%:    %error_message%');
        $this->progress->setMessage($errorMessage, 'error_message');
    }

    protected function stopProgressBar(){
        if($this->progress == null) { return; }
        // ensure that the progress bar is at 100%
        $this->progress->finish();
        $this->output->writeln('');
    }

    protected function isJobEnabled($jobNameKey) {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if($job != null && $job->enabled == 'yes') {
            return true;
        }
        return false;
    }

    protected function setJobStatus($jobNameKey, $status) {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if($job != null) {
            $job->status = $status;
            $job->last_run = ($status == 'stopped') ? date('Y-m-d H:i:s') : $job->last_run;
            $job->save();
            return true;
        }
        return false;
    }

    protected function updateJobTimestamp($jobNameKey) {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        if($job && $job->enabled == 'yes') {
            $job->touch();
            return true;
        }
        return false;
    }

    protected function getJobProperty($jobNameKey, $propName) {

        $job = ScheduledJob::where('command', $this->jobNames[$jobNameKey])->first();
        return $job->getProperty($propName);
    }

    ########################################
    # Creation and maintenance functions
    ########################################

    protected function findOrCreateCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->CID == 0) {
            $legacyCustomer->CID = 1;
        }
        $customer = Customer::find($legacyCustomer->CID);

        if($customer == null) {
            $customer = new Customer;
        }
        return $this->updateCustomer($legacyCustomer, $customer);
    }

    protected function updateCustomer(CustomerOld $legacyCustomer, Customer $customer){

        if($legacyCustomer->CID == 0) {
            $legacyCustomer->CID = 1;
        }
        $customer->id = $legacyCustomer->CID;
        $customer->first_name = $legacyCustomer->Firstname;
        $customer->last_name = $legacyCustomer->Lastname;
        $customer->email = $legacyCustomer->Email;
        $customer->password = $legacyCustomer->Password;
        $customer->company = $legacyCustomer->Company;
        $customer->vip = $legacyCustomer->VIP;
        if($legacyCustomer->AccountStatus == null ||
           $legacyCustomer->AccountStatus == 'DISABLED' ||
           $legacyCustomer->AccountStatus == 'decommissioned') {

            $customer->id_status = config('const.status.disabled');
        } else {
            $customer->id_status = config('const.status.active');
        }
        $customer->signedup_at = $legacyCustomer->DateSignup;
        $customer = $this->copyTimestamps($legacyCustomer, $customer);
        $customer->save();
        return true;
    }

    protected function findOrCreateAddressByCustomer(CustomerOld $legacyCustomer) {

        $address = Address::where('id_customers', $legacyCustomer->CID)
            ->first();

        if($address == null) {
            $address = new Address;
        }
        return $this->updateAddressByCustomer($legacyCustomer, $address);
    }

    protected function updateAddressByCustomer(CustomerOld $legacyCustomer, Address $address) {

        // id should already exist or be auto generated. Do not set it
        $address->address = $legacyCustomer->Address;
        $address->unit = $legacyCustomer->Unit;
        $address->city = $legacyCustomer->City;
        $address->zip = $legacyCustomer->Zip;
        $address->state = $legacyCustomer->State;
        $address->country = $legacyCustomer->Country;
        $address->id_customers = $legacyCustomer->CID;
        $address->id_buildings = $legacyCustomer->LocID;
        $serviceLocation = $legacyCustomer->servicelocations;
        if($serviceLocation != null){
            $address->code = $serviceLocation->ShortName;
        }
        $address = $this->copyTimestamps($legacyCustomer, $address);
        $address->save();
        return true;
    }

    protected function addContactsForCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Email != ''){
            Contact::create(['id_customers' => $legacyCustomer->CID, 'id_types' => 5, 'value' => $legacyCustomer->Email]);
        }

        if($legacyCustomer->Tel != ''){
            Contact::create(['id_customers' => $legacyCustomer->CID, 'id_types' => 1, 'value' => $legacyCustomer->Tel]);
        }
    }

    protected function addNoteForCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Comments != null && $legacyCustomer->Comments != ''){
            Note::create(['id_customers' => $legacyCustomer->CID, 'comment' => $legacyCustomer->Comments, 'created_by' => 1]);
        }
    }

    protected function updateContactByCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Email != ''){
            $emailContact = Contact::firstOrCreate(['id_customers' => $legacyCustomer->CID, 'id_types' => config('const.contact_type.email')]);
            $emailContact->value = $legacyCustomer->Email;
            $emailContact = $this->copyTimestamps($legacyCustomer, $emailContact);
            $emailContact->save();
        }

        if($legacyCustomer->Tel != ''){
            $phoneContact = Contact::firstOrCreate(['id_customers' => $legacyCustomer->CID, 'id_types' => config('const.contact_type.mobile_phone')]);
            $phoneContact->value = $legacyCustomer->Tel;
            $phoneContact = $this->copyTimestamps($legacyCustomer, $phoneContact);
            $phoneContact->save();
        }
    }

    protected function updateNotesByCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Comments != null && $legacyCustomer->Comments != ''){
            $note = Note::firstOrCreate(['id_customers' => $legacyCustomer->CID, 'created_by' => 1]);
            $note->comment = $legacyCustomer->Comments;
            $note->save();
        }
    }

    protected function findOrCreateAddressByBuilding(ServiceLocation $legacyLocation) {

        $address = Address::where('id_buildings', $legacyLocation->LocID)
            ->first();

        if($address == null) {
            $address = new Address;
        }
        return $this->updateAddressByBuilding($legacyLocation, $address);
    }

    protected function updateAddressByBuilding(ServiceLocation $legacyLocation, Address $address) {

        // id should already exist or be auto generated. Do not set it
        $address->address = $legacyLocation->Address;
        $address->city = $legacyLocation->City;
        $address->zip = $legacyLocation->Zip;
        $address->state = $legacyLocation->State;
        $address->country = $legacyLocation->Country;
        $address->id_buildings = $legacyLocation->LocID;
        $address = $this->copyTimestamps($legacyLocation, $address);
        $address->save();
        return true;
    }

    protected function findOrCreatePaymentMethod(CustomerOld $legacyCustomer) {

        $paymentMethod = PaymentMethod::where('id_customers', $legacyCustomer->CID)
            ->first();

        if($paymentMethod == null) {
            $paymentMethod = new PaymentMethod;
        }
        return $this->updatePaymentMethod($legacyCustomer, $paymentMethod);
    }

    protected function updatePaymentMethod(CustomerOld $legacyCustomer, PaymentMethod $paymentMethod) {

        if($legacyCustomer->CCnumber != null && $legacyCustomer->CCnumber != 'XXXX-XXXX-XXXX-' && $legacyCustomer->CCtoken != null) {
            $paymentMethod->id = $legacyCustomer->CID;
            $paymentMethod->account_number = $legacyCustomer->CCtoken;
            $paymentMethod->properties = '[{"last four":"'. $legacyCustomer->CCnumber. '", "card type":"'.
                $legacyCustomer->CCtype. '"}]';
            $paymentMethod->exp_month = $legacyCustomer->Expmo;
            $paymentMethod->exp_year = $legacyCustomer->Expyr;
            $paymentMethod->types = 'Credit Card';
            $paymentMethod->billing_phone = $legacyCustomer->Tel;
            $paymentMethod->priority = 1;
            $paymentMethod->id_customers = $legacyCustomer->CID;
            $paymentMethod->created_at = $legacyCustomer->created_at;
            $paymentMethod->updated_at = $legacyCustomer->updated_at;
            $paymentMethod = $this->copyTimestamps($legacyCustomer, $paymentMethod);
            $address = Address::where('id_customers', $legacyCustomer->CID)->first();
            if($address != null){
                $paymentMethod->id_address = $address->id;
            }

            $paymentMethod->save();
            return true;
        }
        return false;
    }

    protected function findOrCreateBuilding(ServiceLocation $legacyLocation) {

        $building = Building::find($legacyLocation->LocID);

        if($building == null) {
            $building = new Building;
        }

        return $this->updateBuilding($legacyLocation, $building);
    }

    protected function updateBuilding(ServiceLocation $legacyLocation, Building $building){

        if($legacyLocation->LocID == 1){
            return false;
        }
        $building->id = ($legacyLocation->LocID == 0) ? 1 : $legacyLocation->LocID;
        $building->img_building = $legacyLocation->fnImage;
        $building->name = $legacyLocation->Name;
        $building->alias = $legacyLocation->ShortName;
        $building->nickname = $legacyLocation->ShortName;
        $building->id_neighborhoods = 0;
        $building->code = $legacyLocation->ShortName;
        $building->type = $legacyLocation->Type;
        $building->legal_name = $legacyLocation->Name;
        $building->builder = '';
        $building->year_built = date('Y-M-d H:i:s');
        $building->units = ($legacyLocation->Units == null || $legacyLocation->Units == '') ? 0 : $legacyLocation->Units;
        $building->floors = 0;
        $building = $this->copyTimestamps($legacyLocation, $building);
        $building->save();
        return true;
    }

    protected function findOrCreateBuildingProperty(ServiceLocationProperty $legacyLocationProperty) {

        $buildingProperty = BuildingProperty::find($legacyLocationProperty->PropID);

        if($buildingProperty == null) {
            $buildingProperty = new BuildingProperty;
        }

        return $this->updateBuildingProperty($legacyLocationProperty, $buildingProperty);
    }

    protected function updateBuildingProperty(ServiceLocationProperty $legacyLocationProperty, BuildingProperty $buildingProperty){

        $buildingProperty->id = $legacyLocationProperty->PropID;
        $buildingProperty->name = $legacyLocationProperty->FieldTitle;
        $buildingProperty->description = $legacyLocationProperty->Description;
        $buildingProperty = $this->copyTimestamps($legacyLocationProperty, $buildingProperty);
        $buildingProperty->save();
        return true;
    }

    protected function updateBuildingPropertyValues(ServiceLocation $legacyLocation){

        $buildingId = $legacyLocation->LocID;

        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.type'), $legacyLocation->Type);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.units'), $legacyLocation->Units);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.service_type'), $legacyLocation->ServiceType);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.contract_expires'), $legacyLocation->ContractExpire);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.mgmt_company'), $legacyLocation->MgrCompany);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.ethernet'), $legacyLocation->Ethernet);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.wireless'), $legacyLocation->Wireless);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.speeds'), $legacyLocation->Speeds);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.billing'), $legacyLocation->Billing);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.email_service'), $legacyLocation->EmailService);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.ip'), $legacyLocation->IP);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.dns'), $legacyLocation->DNS);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.gateway'), $legacyLocation->Gateway);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.how_to_connect'), $legacyLocation->HowToConnect);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.description'), $legacyLocation->Description);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.support_number'), $legacyLocation->SupportNumber);
        $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.image'), $legacyLocation->fnImage);
        return true;
    }

    protected function findOrCreateBuildingPropertyValue($buildingId, $propertyId, $value) {

        $building = Building::find($buildingId);
        $buildingPropertyValue = BuildingPropertyValue::firstOrCreate(['id_buildings' => $buildingId, 'id_building_properties' => $propertyId]);
        $buildingPropertyValue->value = $value;

        if($building != null){
            $buildingPropertyValue->created_at = $building->created_at;
            $buildingPropertyValue->updated_at = $building->updated_at;
        }

        $buildingPropertyValue->save();
        return true;
    }

    protected function findOrCreateProduct(ProductOld $legacyProduct) {

        $product = Product::find($legacyProduct->ProdID);

        if($product == null) {
            $product = new Product;
        }
        return $this->updateProduct($legacyProduct, $product);
    }

    protected function updateProduct(ProductOld $legacyProduct, Product $product){

        $product->id = $legacyProduct->ProdID;
        $product->name = $legacyProduct->ProdName;
        $product->description = $legacyProduct->ProdDescription;
        $product->amount = $legacyProduct->Amount;
        $product->frequency = $legacyProduct->ChargeFrequency;
        $product->id_products = $legacyProduct->ParentProdID;
        $product = $this->copyTimestamps($legacyProduct, $product);

        switch ($legacyProduct->ProdType) {

            case 'Internet':
                $product->id_types = config('const.type.internet');
                break;
            case 'Phone':
                $product->id_types = config('const.type.phone');
                break;
            case 'Phone-Option':
                $product->id_types = config('const.type.phone_option');
                break;
            case 'Router':
                $product->id_types = config('const.type.customer_router');
                break;
            case 'Ethernet Jack':
                $product->id_types = config('const.type.ethernet_jack');
                break;
            case 'Other':
                $product->id_types = config('const.type.other');
                break;
            case 'Cable Run':
                $product->id_types = config('const.type.cable_run');
                break;
            case 'Activation Fee':
                $product->id_types = config('const.type.activation_fee');
                break;
            default:
                break;
        }

        $product->save();
        return true;
    }

    protected function findOrCreateProductProperty(ProductPropertyOld $legacyProductProperty) {

        $productProperty = ProductProperty::find($legacyProductProperty->PropID);

        if($productProperty == null) {
            $productProperty = new ProductProperty;
        }
        return $this->updateProductProperty($legacyProductProperty, $productProperty);
    }

    protected function updateProductProperty(ProductPropertyOld $legacyProductProperty, ProductProperty $productProperty) {

        $productProperty->id = $legacyProductProperty->PropID;
        $productProperty->name = $legacyProductProperty->FieldTitle;
        $productProperty->description = $legacyProductProperty->Description;
        $productProperty = $this->copyTimestamps($legacyProductProperty, $productProperty);
        $productProperty->save();
        return true;
    }

    protected function findOrCreateProductPropertyValue(ProductPropertyOld $legacyProductProperty) {

        $productPropertyValue = ProductPropertyValue::find($legacyProductPropertyValue->VID);

        if($productPropertyValue == null) {
            $productPropertyValue = new ProductPropertyValue;
        }
        return $this->updateProductPropertyValue($legacyProductProperty, $productPropertyValue);
    }

    protected function updateProductPropertyValue(ProductPropertyValueOld $legacyProductPropertyValue, ProductPropertyValue $productPropertyValue) {

        $productPropertyValue->id = $legacyProductPropertyValue->VID;
        $productPropertyValue->id_products = $legacyProductPropertyValue->ProdID;
        $productPropertyValue->id_product_properties = $legacyProductPropertyValue->PropID;
        $productPropertyValue->value = $legacyProductPropertyValue->Value;
        $productPropertyValue = $this->copyTimestamps($legacyProductPropertyValue, $productPropertyValue);
        $productPropertyValue->save();
        return true;
    }

    protected function findOrCreateCustomerProduct(CustomerProductOld $legacyCustomerProduct) {

        $customerProduct = CustomerProduct::find($legacyCustomerProduct->CSID);

        if($customerProduct == null) {
            $customerProduct = new CustomerProduct;
        }
        return $this->updateCustomerProduct($legacyCustomerProduct, $customerProduct);
    }

    protected function updateCustomerProduct(CustomerProductOld $legacyCustomerProduct, CustomerProduct $customerProduct) {

        if($legacyCustomerProduct->CID == null || $legacyCustomerProduct->CID == '') {
            return false;
        }

        $customerProduct->id = $legacyCustomerProduct->CSID;
        $customerProduct->id_customers = $legacyCustomerProduct->CID;
        $customerProduct->id_products = $legacyCustomerProduct->ProdID;
        $customerProduct->id_customer_products = $legacyCustomerProduct->ParentCSID;
        $customerProduct->signed_up = $legacyCustomerProduct->CProdDateSignup;
        $customerProduct->expires = $legacyCustomerProduct->CProdDateExpires;
        $customerProduct->renewed_at = $legacyCustomerProduct->CProdDateRenewed;
        $customerProduct->id_users = $legacyCustomerProduct->UpdatedByID;
        $customerProduct->last_charged = $legacyCustomerProduct->CProdLastCharged;
        $customerProduct->invoice_status = 0;
        $customerProduct->amount_owed = 0;

        switch ($legacyCustomerProduct->Status) {

            case 'active':
                $customerProduct->id_status = config('const.status.active');
                break;
            case 'disabled':
                $customerProduct->id_status = config('const.status.disabled');
                break;
            case 'new':
                $customerProduct->id_status = config('const.status.new');
                break;
            case 'decommissioned':
                $customerProduct->id_status = config('const.status.decommissioned');
                break;
            default:
                break;
        }

        $address = Address::where('id_customers', $legacyCustomerProduct->CID)->first();
        if($address != null){
            $customerProduct->id_address = $address->id;
        }

        $customerProduct = $this->copyTimestamps($legacyCustomerProduct, $customerProduct);
        $customerProduct->save();
        return true;
    }

    protected function findOrCreateBuildingProduct(ServiceLocationProduct $legacyBuildingProduct) {

        $buildingProduct = BuildingProduct::find($legacyBuildingProduct->SLPID);

        if($buildingProduct == null) {
            $buildingProduct = new BuildingProduct;
        }
        return $this->updateBuildingProduct($legacyBuildingProduct, $buildingProduct);
    }

    protected function updateBuildingProduct(ServiceLocationProduct $legacyBuildingProduct, BuildingProduct $buildingProduct) {

        $buildingProduct->id = $legacyBuildingProduct->SLPID;
        $buildingProduct->id_buildings = $legacyBuildingProduct->LocID;
        $buildingProduct->id_products = $legacyBuildingProduct->ProdID;
        $buildingProduct = $this->copyTimestamps($legacyBuildingProduct, $buildingProduct);
        $buildingProduct->save();
        return true;
    }

    protected function findOrCreateNetworkNode(NetworkNodeOld $legacyNetworkNode) {

        $networkNode = NetworkNode::find($legacyNetworkNode->NodeID);

        if($networkNode == null) {
            $networkNode = new NetworkNode;
        }
        return $this->updateNetworkNode($legacyNetworkNode, $networkNode);
    }

    protected function updateNetworkNode(NetworkNodeOld $legacyNetworkNode, NetworkNode $networkNode) {

        $networkNode->id = $legacyNetworkNode->NodeID;
        $networkNode->ip_address = $legacyNetworkNode->IPAddress;
        $networkNode->mac_address = $legacyNetworkNode->MacAddress;
        $networkNode->host_name = $legacyNetworkNode->HostName;
        $networkNode->id_address = $legacyNetworkNode->LocID;
        $networkNode->vendor = $legacyNetworkNode->Vendor;
        $networkNode->model = $legacyNetworkNode->Model;
        $networkNode->role = $legacyNetworkNode->Role;
        $networkNode->properties = $legacyNetworkNode->Properties;
        $networkNode->comments = $legacyNetworkNode->Comments;

        switch ($legacyNetworkNode->Type) {

            case 'Router':
                $networkNode->id_types = config('const.type.router');
                break;
            case 'Switch':
                $networkNode->id_types = config('const.type.switch');
                break;
            default:
                break;
        }
        $networkNode = $this->copyTimestamps($legacyNetworkNode, $networkNode);
        $networkNode->save();
        return true;
    }

    protected function findOrCreatePort(DataServicePort $legacyPort) {

        $port = Port::find($legacyPort->PortID);

        if($port == null) {
            $port = new Port;
        }
        return $this->updatePort($legacyPort, $port);
    }

    protected function updatePort(DataServicePort $legacyPort, Port $port) {

        if($legacyPort->NodeID == null || $legacyPort->NodeID == '') {
            return false;
        }

        $port->port_number = $legacyPort->PortNumber;
        $port->access_level = $legacyPort->Access;
        $port->id_network_nodes = $legacyPort->NodeID;

        $legacyCustomer = CustomerOld::where('PortID', $legacyPort->PortID)->first();
        if($legacyCustomer != null){
            $port->id_customers = $legacyCustomer->CID;
        }

        $port = $this->copyTimestamps($legacyPort, $port);
        $port->save();
        return true;
    }

    protected function findOrCreateReason(SupportTicketReason $legacyReason) {

        $reason = Reason::find($legacyReason->RID);

        if($reason == null) {
            $reason = new Reason;
        }
        return $this->updateReason($legacyReason, $reason);
    }

    protected function updateReason(SupportTicketReason $legacyReason, Reason $reason) {

        if($legacyReason->RID == 0) {
            $legacyReason->RID = 1;
        } else if($legacyReason->RID == 1) {
            $legacyReason->RID = 30;
        }

        $reason->id = $legacyReason->RID;
        $reason->name = $legacyReason->ReasonName;
        $reason->short_description = $legacyReason->ReasonShortDesc;
        $reason->id_categories = $legacyReason->ReasonCategory;
        $reason->description = $legacyReason->ReasonDescription;

        switch ($legacyReason->ReasonCategory) {

            case 'INT':
                $reason->id_categories = config('const.reason_category.internet');
                break;
            case 'PH':
                $reason->id_categories = config('const.reason_category.phone');
                break;
            case 'MISC':
                $reason->id_categories = config('const.reason_category.misc');
                break;
            case 'TV';
                $reason->id_categories = config('const.reason_category.tv');
                break;
            default:
                break;
        }
        $reason->created_at = $legacyReason->created_at;
        $reason->updated_at = $legacyReason->updated_at;
        $reason = $this->copyTimestamps($legacyReason, $reason);
        $reason->save();
        return true;
    }

    protected function findOrCreateTicket(SupportTicket $legacyTicket) {

        $ticket = Ticket::find($legacyTicket->TID);

        if($ticket == null) {
            $ticket = new Ticket;
        }
        return $this->updateTicket($legacyTicket, $ticket);
    }

    protected function updateTicket(SupportTicket $legacyTicket, Ticket $ticket) {

        $ticket->id = $legacyTicket->TID;
        $ticket->id_customers = ($legacyTicket->CID == 0) ? 1 : $legacyTicket->CID;
        $ticket->ticket_number = $legacyTicket->TicketNumber;
        $ticket->vendor_ticket = $legacyTicket->VendorTID;
        $legacyTicketReason = trim($legacyTicket->RID);
        $ticket->id_reasons = ($legacyTicketReason == null || $legacyTicketReason == '') ? config('const.reason.unknown') :  $legacyTicketReason;
        $ticket->comment = $legacyTicket->Comment;
        $ticket->status = $legacyTicket->Status;
        $ticket->id_users = ($legacyTicket->StaffID == '') ? 0 : $legacyTicket->StaffID;
        $ticket->id_users_assigned = ($legacyTicket->AssignedToID == '') ? 0 : $legacyTicket->AssignedToID;
        $ticket = $this->copyTimestamps($legacyTicket, $ticket);

        if($legacyTicket->RID == 1) {
            $ticket->id_reasons = config('const.reason.internal_billing');
        }

        if($legacyTicket->RID == 0) {
            $ticket->id_reasons = config('const.reason.unknown');
        }
        $ticket->save();
        return true;
    }

    protected function findOrCreateTicketHistory(SupportTicketHistory $legacyTicketHistory) {

        $ticketHistory = TicketHistory::find($legacyTicketHistory->THID);

        if($ticketHistory == null) {
            $ticketHistory = new TicketHistory;
        }
        return $this->updateTicketHistory($legacyTicketHistory, $ticketHistory);
    }

    protected function updateTicketHistory(SupportTicketHistory $legacyTicketHistory, $ticketHistory = null) {

        if($ticketHistory == null) { $ticketHistory = new TicketHistory; }
        $ticketHistory->id = $legacyTicketHistory->THID;
        $ticketHistory->id_tickets = $legacyTicketHistory->TID;
        $legacyTicketHistoryReason = trim($legacyTicketHistory->RID);
        $ticketHistory->id_reasons = ($legacyTicketHistoryReason == null || $legacyTicketHistoryReason == '') ? config('const.reason.unknown') :  $legacyTicketHistoryReason;
        $ticketHistory->comment = $legacyTicketHistory->Comment;
        $ticketHistory->status = $legacyTicketHistory->Status;
        $ticketHistory->id_users = ($legacyTicketHistory->StaffID == '') ? 0 : $legacyTicketHistory->StaffID;
        $ticketHistory->id_users_assigned = ($legacyTicketHistory->AssignedToID == '') ? 0 : $legacyTicketHistory->AssignedToID;

        if($legacyTicketHistory->RID == 1) {
            $ticketHistory->id_reasons = config('const.reason.internal_billing');
        }

        if($legacyTicketHistory->RID == 0) {
            $ticketHistory->id_reasons = config('const.reason.unknown');
        }
        $ticketHistory = $this->copyTimestamps($legacyTicketHistory, $ticketHistory);
        $ticketHistory->save();
        //        $ticketHistory = null;
        return true;
    }

    protected function findOrCreateTransactionLog(BillingTransactionLogOld $legacyTransactionLog) {

        $transactionLog = BillingTransactionLog::find($legacyTransactionLog->LogID);

        if($transactionLog == null) {
            $transactionLog = new BillingTransactionLog;
        }
        return $this->updateTransactionLog($legacyTransactionLog, $transactionLog);
    }

    protected function updateTransactionLog(BillingTransactionLogOld $legacyTransactionLog, BillingTransactionLog $transactionLog) {

        $transactionLog->id = $legacyTransactionLog->LogID;
        $transactionLog->date_time = $legacyTransactionLog->DateTime;
        $transactionLog->transaction_id = $legacyTransactionLog->TransactionID;
        $transactionLog->username = $legacyTransactionLog->Username;
        $transactionLog->id_customers = $legacyTransactionLog->CID;
        $transactionLog->name = $legacyTransactionLog->Name;
        $transactionLog->amount = $legacyTransactionLog->Amount;
        $transactionLog->transaction_type = $legacyTransactionLog->TransType;
        $transactionLog->payment_mode = $legacyTransactionLog->PaymentMode;
        $transactionLog->order_number = $legacyTransactionLog->OrderNumber;
        $transactionLog->charge_description = $legacyTransactionLog->ChargeDescription;
        $transactionLog->charge_details = $legacyTransactionLog->ChargeDetails;
        $transactionLog->action_code = $legacyTransactionLog->ActionCode;
        $transactionLog->approval = $legacyTransactionLog->Approval;
        $transactionLog->verification = $legacyTransactionLog->Verification;
        $transactionLog->response_text = $legacyTransactionLog->Responsetext;
        $transactionLog->response_error = $legacyTransactionLog->Responseerror;
        $transactionLog->address = $legacyTransactionLog->Address;
        $transactionLog->unit = $legacyTransactionLog->Unit;
        $transactionLog->comment = $legacyTransactionLog->Comment;
        $transactionLog = $this->copyTimestamps($legacyTransactionLog, $transactionLog);
        $transactionLog->save();
        return true;
    }

    protected function findOrCreateNetworkTab(NetworkTabOld $legacyNetworkTab) {

        $networkTab = NetworkTab::find($legacyNetworkTab->NID);

        if($networkTab == null) {
            $networkTab = new NetworkTab;
        }
        return $this->updateNetworkTab($legacyNetworkTab, $networkTab);
    }

    protected function updateNetworkTab(NetworkTabOld $legacyNetworkTab, NetworkTab $networkTab) {

        $networkTab->id = $legacyNetworkTab->NID;
        $networkTab->location = $legacyNetworkTab->location;
        $networkTab->address = $legacyNetworkTab->address;
        $networkTab->core = $legacyNetworkTab->core;
        $networkTab->dist = $legacyNetworkTab->dist;
        $networkTab->primary = $legacyNetworkTab->primary;
        $networkTab->backup = $legacyNetworkTab->backup;
        $networkTab->mgmt_net = $legacyNetworkTab->mgmtnet;
        $networkTab = $this->copyTimestamps($legacyNetworkTab, $networkTab);
        $networkTab->save();
        return true;
    }
}
?>
