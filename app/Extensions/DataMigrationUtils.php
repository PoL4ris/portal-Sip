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
use App\Models\CustomerPort;
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

        $this->writeLog('<info> Seeding data_migrations table</info>');

        if(DataMigration::count() != 0){
            $this->writeLog('<info> data_migrations table is not empty. Skipping.</info>');
            return;
        }

        foreach($this->tableMap as $tableName => $tableModelMap){
            DataMigration::firstOrCreate(['table_name' => $tableName]);
        }
    }

    public function seedCategoriesTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding categories table</info>');
        }
        if(Category::count() != 0){
            $this->writeLog('<info> categories table is not empty. Skipping.</info>');
            return;
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
        if(App::count() != 0){
            $this->writeLog('<info> apps table is not empty. Skipping.</info>');
            return;
        }
        App::firstOrCreate(['id' => 1, 'id_apps' => 0, 'name' => 'Dashboard', 'icon' => 'fa-dashboard', 'url' => 'dashboard']);
        App::firstOrCreate(['id' => 2, 'id_apps' => 0, 'name' => 'Support', 'icon' => 'fa-wrench', 'url' => 'support']);
        App::firstOrCreate(['id' => 3, 'id_apps' => 0, 'name' => 'Customers', 'icon' => 'fa-user ', 'url' => 'customers']);
        App::firstOrCreate(['id' => 4, 'id_apps' => 0, 'name' => 'Building', 'icon' => 'fa-building-o', 'url' => 'buildings']);
        App::firstOrCreate(['id' => 5, 'id_apps' => 0, 'name' => 'Network', 'icon' => 'fa-signal', 'url' => 'network']);
        App::firstOrCreate(['id' => 6, 'id_apps' => 0, 'name' => 'Admin', 'icon' => 'icon-lock', 'url' => 'admin']);
        App::firstOrCreate(['id' => 7, 'id_apps' => 0, 'name' => 'Partners', 'icon' => 'fa-exchange ', 'url' => 'partners']);
        App::firstOrCreate(['id' => 8, 'id_apps' => 0, 'name' => 'Sales', 'icon' => 'fa-cubes', 'url' => 'sales']);
        App::firstOrCreate(['id' => 9, 'id_apps' => 0, 'name' => 'Reports', 'icon' => 'fa-bar-chart-o', 'url' => 'reports']);
        App::firstOrCreate(['id' => 10, 'id_apps' => 0, 'name' => 'Billing', 'icon' => 'fa-shopping-cart', 'url' => 'billing']);
        App::firstOrCreate(['id' => 11, 'id_apps' => 0, 'name' => 'Partners', 'icon' => 'fa-exchange', 'url' => 'partners']);
        App::firstOrCreate(['id' => 12, 'id_apps' => 0, 'name' => 'Contracts', 'icon' => 'fa-book', 'url' => 'contracts']);
        App::firstOrCreate(['id' => 13, 'id_apps' => 0, 'name' => 'Field Ops', 'icon' => 'fa-tree', 'url' => 'fieldops']);
        App::firstOrCreate(['id' => 14, 'id_apps' => 0, 'name' => 'Survery', 'icon' => 'fa-space-shuttle', 'url' => 'survery']);

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
        if(User::count() != 0){
            $this->writeLog('<info> users table is not empty. Skipping.</info>');
            return;
        }
        User::firstOrCreate(['id' => 1, 'first_name' => 'Farzad', 'last_name' => 'Moeinzadeh', 'email' => 'farzad@silverip.com', 'social_access' => 1, 'alias' => 'FM', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 2, 'first_name' => 'Peyman', 'last_name' => 'Pourkermani', 'email' => 'peyman@silverip.com', 'social_access' => 1, 'alias' => 'PP', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 3, 'first_name' => 'Jon', 'last_name' => 'Nierstheimer', 'email' => 'jon@silverip.com', 'social_access' => 1, 'alias' => 'JN', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 5, 'first_name' => 'James', 'last_name' => 'Manrique', 'email' => 'james@silverip.com', 'social_access' => 1, 'alias' => 'JM', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 7, 'first_name' => 'Todd', 'last_name' => 'Hammersmith', 'email' => 'todd@silverip.com', 'social_access' => 1, 'alias' => 'TH', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 8, 'first_name' => 'Andrew', 'last_name' => 'Perez', 'email' => 'andrew@silverip.com', 'social_access' => 1, 'alias' => 'AP', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 9, 'first_name' => 'Abraham', 'last_name' => 'Ramirez', 'email' => 'abe@silverip.com', 'social_access' => 1, 'alias' => 'Abe', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 12, 'first_name' => 'Eliazar', 'last_name' => 'Padilla', 'email' => 'eli@silverip.com', 'social_access' => 1, 'alias' => 'EP', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 11, 'first_name' => 'Rob', 'last_name' => 'Czajewski', 'email' => 'rob@silverip.com', 'social_access' => 1, 'alias' => 'Rob', 'id_status' => 2, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 14, 'first_name' => 'Juan', 'last_name' => 'Quintanilla', 'email' => 'juan@silverip.com', 'social_access' => 1, 'alias' => 'JQ', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 15, 'first_name' => 'Sergiu', 'last_name' => 'Rudenco', 'email' => 'sergiu@silverip.com', 'social_access' => 1, 'alias' => 'SR', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 16, 'first_name' => 'Vanessa', 'last_name' => 'Phillips', 'email' => 'vanessa@silverip.com', 'social_access' => 1, 'alias' => 'VP', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 17, 'first_name' => 'Marline', 'last_name' => 'Sanders', 'email' => 'marline@silverip.com', 'social_access' => 1, 'alias' => 'MS', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 18, 'first_name' => 'SIP', 'last_name' => 'Support', 'email' => 'help@silverip.com', 'social_access' => 1, 'alias' => 'Support', 'id_status' => 2, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 19, 'first_name' => 'Brian', 'last_name' => 'Collazo', 'email' => 'brian.c@silverip.com', 'social_access' => 1, 'alias' => 'BC', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 20, 'first_name' => 'Brian', 'last_name' => 'Craddock', 'email' => 'brian@silverip.com', 'social_access' => 1, 'alias' => 'BCR', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 21, 'first_name' => 'Melvin', 'last_name' => 'Mendoza', 'email' => 'melvin@silverip.com', 'social_access' => 1, 'alias' => 'MM', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 22, 'first_name' => 'Israel', 'last_name' => 'Perez', 'email' => 'izzy@silverip.com', 'social_access' => 1, 'alias' => 'IP', 'id_status' => 1, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 23, 'first_name' => 'Pablo', 'last_name' => 'Laris', 'email' => 'pablo@silverip.com', 'social_access' => 1, 'alias' => 'PL', 'id_status' => 1, 'id_profiles' => 1]);

        //        User::firstOrCreate(['id' => 1, 'first_name' => 'Pablo', 'last_name' => 'Laris', 'email' => 'pablo@silverip.com', 'password' => '$2y$10$u.sqr/WkAQaJL7FCCQVmGue8efy3wAdF1E/OKGr5XQgxS8vDPCJ.2', 'remember_token' => 'Jonk6hBM0kmkQipY2WTTmrfFN2YZrZDQzzj37GqwTObdij5LOOUnjFeLF8Qb', 'social_token' => 'ya29.Gl0WBAIa60ypaAaSdSo-rfT08I_1X4poFF9TM3MgF2Bs3E83GYaYoTiTP2ljuN_n4dP6sf6ZDhNBzYp9zo2I-Vl7D-VWkmk', 'social_access' => 1, 'avatar' => 'https://lh4.googleusercontent.com/-MY1G9QEJ8M4/AAAAAAAAAAI/AAAAAAAAABM/wLAg7FpkX5E/photo.jpg?sz=50', 'alias' => 'PL', 'id_status' => 3, 'id_profiles' => 1]);
        //        User::firstOrCreate(['id' => 2, 'first_name' => 'Farzad', 'last_name' => 'Farzad', 'email' => 'farzad@silverip.com', 'password' => '$2a$08$UdmCQyKxZlWEfbl94yB4e.M4zS9lg88Osea8lQ8Ay0NQ9KxD05Rkq', 'remember_token' => 'DN5OipGB4aH2dU233R3hAKnCQ1qoeqSN1VnQHnxJPHyKLnvg5ldpYdPU8Qov', 'social_token' => '', 'social_access' => 1, 'avatar' => 'https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50', 'alias' => 'FM', 'id_status' => 3, 'id_profiles' => 1]);
        //        User::firstOrCreate(['id' => 3, 'first_name' => 'Peyman', 'last_name' => 'Pourkermani', 'email' => 'peyman@silverip.com', 'password' => '$2y$10$12q8f3L/WXUiwRpQdYjOX.7HzTx/7akNIfqItbJncfYCr5z5egEjO', 'remember_token' => 'MA1xsnRUhbTRteuxmziO9Pe1eBp74nzBifwRhC2cmkSyu45ZA7TvYOKK2Khq', 'social_token' => 'ya29.CjNrA0b1vBMPk49P_gZ9TOSlV7ajVYFVixiKE6py_eBOcgL7isD2bFuIrxzO-CiSlre8r7g', 'social_access' => 1, 'avatar' => 'https://lh6.googleusercontent.com/-2JxSuRyHszI/AAAAAAAAAAI/AAAAAAAAABA/FXimdQSdkwQ/photo.jpg?sz=50', 'alias' => 'PP', 'id_status' => 3, 'id_profiles' => 1]);
        User::firstOrCreate(['id' => 24, 'first_name' => 'admin', 'last_name' => 'admin', 'email' => 'admin@admin.com', 'password' => '$2a$06$lRhl6zzwSxCKUrGPKAWM0OL6MgYECwjB6Hv02zPOGsGThmmKjINl2', 'remember_token' => '', 'social_token' => '', 'social_access' => 1, 'avatar' => '', 'alias' => 'A', 'id_status' => 3, 'id_profiles' => 1]);
    }

    public function seedStatusTable() {
        if($this->output != null){
            $this->output->writeln('<info> Seeding status table</info>');
        }
        if(Status::count() != 0){
            $this->writeLog('<info> status table is not empty. Skipping.</info>');
            return;
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
        if(Type::count() != 0){
            $this->writeLog('<info> types table is not empty. Skipping.</info>');
            return;
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
        if(ContactType::count() != 0){
            $this->writeLog('<info> contacts table is not empty. Skipping.</info>');
            return;
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
        if(BuildingProperty::count() != 0){
            $this->writeLog('<info> building_properties table is not empty. Skipping.</info>');
            return;
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
        if(Neighborhood::count() != 0){
            $this->writeLog('<info> neighborhoods table is not empty. Skipping.</info>');
            return;
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
                return $legacyDataModelName::where('updated_at', '!=', $lastUpdateTimestamp)
                    ->where($legacyModelId, '>', $startingId);
            }
            return $legacyDataModelName::where('updated_at', '>', $lastUpdateTimestamp)
                ->where($legacyModelId, '>', $startingId);
        };
        $totalUpdateRecords = $updateQueryBuilder($legacyDataModelName, $lastUpdateTimestamp, $legacyModelId)->count();

        $lastCreateTimestamp = $dataMigration->max_created_at;
        $createQueryBuilder = function($legacyDataModelName, $lastCreateTimestamp, $legacyModelId, $startingId = -1){
            if($lastCreateTimestamp == null){
                return $legacyDataModelName::where('created_at', '!=', $lastCreateTimestamp)
                    ->where($legacyModelId, '>', $startingId);
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

                $updateCount = 0;
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

                $createCount = 0;
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

    protected function writeLog($message){
        if($this->output != null){
            $this->output->writeln($message);
        } else {
            Log::info($message);
        }
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
            ->whereNull('unit')
            ->whereNull('id_customers')
            ->first();

        if($address == null) {
            $address = new Address;
        }
        return $this->updateAddressByBuilding($legacyLocation, $address);
    }

    protected function updateAddressByBuilding(ServiceLocation $legacyLocation, Address $address) {

        // id should already exist or be auto generated. Do not set it
        $address->address = $legacyLocation->Address;
        $address->code = $legacyLocation->ShortName;
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

        if($legacyLocation->LocID == 0){
            $legacyLocation->LocID = 1;
        }
        $building = Building::find($legacyLocation->LocID);

        if($building == null) {
            $building = new Building;
        }

        return $this->updateBuilding($legacyLocation, $building);
    }

    protected function updateBuilding(ServiceLocation $legacyLocation, Building $building){

        if($legacyLocation->LocID == 1 && $legacyLocation->ShortName == '474L'){
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
        $this->updateBuildingUnitNumbers($buildingId);
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

        if($legacyBuildingProduct->Status == 'active') {
            $buildingProduct->id_status = config('const.status.active');
        } else {
            $buildingProduct->id_status = config('const.status.disabled');
        } 

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
        $networkNode->vendor = $legacyNetworkNode->Vendor;
        $networkNode->model = $legacyNetworkNode->Model;
        $networkNode->role = $legacyNetworkNode->Role;
        $networkNode->properties = $legacyNetworkNode->Properties;
        $networkNode->comments = $legacyNetworkNode->Comments;

        $address = Address::where('id_buildings', $legacyNetworkNode->LocID)
            ->whereNull('unit')
            ->whereNull('id_customers')
            ->first();

        if($address != null) {
            $networkNode->id_address = $address->id;
        }

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

        $legacyCustomers = CustomerOld::where('PortID', $legacyPort->PortID)
            ->orderBy('AccountStatus', 'asc')
            ->get();

        if($legacyCustomers->count() > 0){
            $legacyCustomer = $legacyCustomers->first();
            $port->id_customers = $legacyCustomer->CID;
        }

        $port = $this->copyTimestamps($legacyPort, $port);
        $port->save();

        foreach($legacyCustomers as $legacyCustomer) {
            CustomerPort::firstOrCreate(['customer_id' => $legacyCustomer->CID, 'port_id' => $port->id]);
        }

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
            $ticket->id_reasons = config('const.reason.move_in_howto_connect');
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

    protected function updateBuildingUnitNumbers($buildingId){

        $unitNumberMap = array(
            '159W' =>  array('5A', '5B', '5C', '5D', '5F', '5G', '6A', '6B', '6C', '6D', '6E', '6F', '6G', '7A', '7B', '7C', '7D', '7E', '7F', '7G', '8A', '8B', '8C', '8D', '8E', '8F', '8G', '9A', '9B', '9C', '9D', '9E', '9F', '9G', '10A', '10B', '10C', '10D', '10E', '10F', '10G', '11A', '11B', '11C', '11D', '11E', '11F', '11G', '12A', '12B', '12C', '12D', '12E', '12F', '12G', '13A', '13B', '13C', '13D', '13E', '13F', '13G', '14A', '14B', '14C', '14D', '14E', '14F', '15A', '15B', '15C', '15D', '15E', '15F', '16A', '16B', '16C', '16D', '16E', '17A', '17B', '17C', '18A', '18B', '19A', '19B', '20A', '20B', '21A', '21B', '22A', '23A', '24A', '25A', '26A', '27A', '28A', '29A', '30A', '31A', '32A', '33A', '34A', '35A', 'PH'),
            '1600P' =>  array('601', '602', '604', '605', '607', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1310', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1701', '1702', '1704', '1705', '1706', '1707', '1708', '1709', '1801', '1802', '1804', '1805', '1806', '1807', '1808', '1809', '1901', '1902', '1904', '1905', '1906', '1907', '1908', '1909', '2001', '2002', '2004', '2005', '2006', '2007', '2008', '2009', '2101', '2102', '2104', '2105', '2106', '2107', '2108', '2109', '2201', '2202', '2204', '2205', '2207', '2301', '2304', '2307', '2309', '2401', '2404', 'TH01', 'TH02', 'TH03', 'TH04', 'TH05'),
            '659R' =>  array('401','402','403','404','407','408','409','410','411','412','413','414','419','420','421','501','502','503','504','507','508','509','510','511','512','513','514','515','516','517','518','519','520','601','602','603','604','607','608','609','610','611','612','613','614','615','616','617','618','619','620','701','702','703','704','707','708','709','710','711','712','713','714','715','716','717','718','719','720','801','802','803','804','807','808','809','810','811','812','813','814','815','816','817','818','819','820','901','902','903','904','907','908','909','910','911','912','913','914','915','916','917','918','919','920','1001','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1016','1017','1018','1019','1020','1101','1105','1106','1107','1108','1109','1110','1111','1112','1113','1114','1115','1116','1117','1118','1119','1120','1201','1205','1206','1207','1208','1209','1210','1211','1212','1213','1214','1215','1216','1217','1218','1219','1220','1401','1405','1406','1407','1408','1409','1410','1411','1412','1413','1414','1415','1416','1417','1418','1419','1420','1501','1505','1506','1507','1508','1509','1510','1511','1512','1513','1514','1515','1516','1517','1518','1519','1520','1601','1605','1606','1607','1608','1609','1610','1611','1612','1613','1614','1615','1616','1617','1618','1619','1620','1622','1701','1705','1706','1707','1708','1709','1710','1711','1712','1713','1714','1715','1716','1717','1718','1719','1720','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1812','1813'),
            '65M' =>  array( '4001','4002','4003','4004','4005','4006','4007','4008','4009','4010','4011','4012','4013','4014','4015','4016','4018','4020','4021','4022','4023','4024','4025','4026','4101','4103','4104','4105','4106','4107,4108','4109','4110,4111','4112','4113','4114','4115','4116','4118','4120','4121','4122','4123','4124','4125','4201','4202','4203','4204','4205','4206','4207','4208','4209','4210','4211','4212','4213','4214','4215','4216','4217','4218','4219','4220','4221','4222','4223','4224','4225','4226','4301','4302','4303','4304','4305','4306','4307','4308','4309','4310','4311','4312','4313','4314','4315','4316','4317','4318','4319','4320','4321','4322','4323','4324','4325','4326','4401','4402','4403','4404','4405','4406','4407','4408','4409','4410','4411','4412','4413','4414','4415','4416','4417','4418','4419','4420','4421','4422','4423','4424','4425','4426','4501','4502','4503','4504','4505','4506','4507','4508','4509','4510','4511','4512','4513','4514','4515','4516','4517','4518','4519','4520','4521','4522','4523','4524','4525','4526','4601','4603','4604','4605','4606','4607','4608','4609','4610','4611','4612','4613','4614','4615','4616','4617','4618','4619','4620','4621','4622','4623','4624','4625','4626','4701','4702','4703','4704','4705','4706','4707','4708','4709','4710','4711','4712','4713','4714','4715','4716','4717','4718','4719','4721','4722','4723','4724','4725','4726','4801','4802','4803','4804','4805','4806','4807','4808','4809','4810','4811','4812'),
            '360R' =>  array('301', '302', '303', '304', '305', '306', '307', '308', '401', '402', '403', '404', '405', '406', '407', '408', '501', '502', '503', '504', '505', '506', '507', '508', '601', '602', '603', '604', '605', '606', '607', '608', '701', '702', '703', '704', '705', '706', '707', '708', '801', '802', '803', '804', '805', '806', '807', '808', '901', '902', '903', '904', '905', '906', '907', '908', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2701', '2702', '2703', '2704', '2705', '2706', '2707', '2708', '2801', '2802', '2803', '2804', '2805', '2806', '2807', '2808', '2901', '2902', '2903', '2904', '2905', '2906', '2907', '3001', '3002', '3003', '3004', '3005', '3006', '3007', '3101', '3102', '3103', '3104', '3105', '3106', '3107', '3201', '3202', '3203', '3204', '3205', '3206', '3207', '3301', '3302', '3303', '3304', '3305', '3306', '3307', '3401', '3402', '3403', '3404', '3405', '3406', '3407', '3501', '3502', '3503', '3504', '3505', '3506', '3507', '3601', '3602', '3603', '3604', '3605', '3606', '3607', '3701', '3702', '3703', '3704', '3705', '3706', '3707', '3801', '3802', '3803', '3804', '3805', '3806', '3807', '3901', '3902', '3903', '3904', '3905', '3906', '3907', '4001', '4002', '4003', '4004', '4005', '4006', '4007', '4101', '4102', '4103', '4104', '4105', '4106', '4107', '4201', '4202', '4203', '4204', '4205', '4206', '4207', '4301', '4302', '4303', '4304', '4305', '4306', '4307'),
            '1601I' =>  array('101','102','103','104','105','106','107','108','109','110','111','112','113','201','202','203','204','205','206','207','208','209','210','211','212','213','214','215','216','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315','316','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','501','502','503','504','505'),
            '340R' =>  array('201','202','203','204','205','206','207','300','301','302','303','304','305','306','307','400','401','402','403','404','405','406','407','500','501','502','503','504','505','506','507','600','601','602','603','604','605','606','607','700','701','702','703','704','705','706','707','800','801','802','803','804','805','806','807','900','901','902','903','904','905','906','907','1000','1001','1002','1003','1004','1005','1006','1007','1100','1101','1102','1103','1104','1105','1106','1107','1200','1201','1202','1203','1204','1205','1206','1207','1300','1301','1302','1303','1304','1305','1306','1307','1400','1401','1402','1403','1404','1405','1406','1407','1501','1502','1504','1505','1506','1601','1602','1603','1604','1605','1606','1701','1702','1703','1704','1705','1706','1801','1802','1803','1804','1805','1806','1901','1902','1903','1904','1905','1906','2001','2002','2003','2004','2005','2006','2101','2102','2103','2104','2105','2106','2201','2202','2203','2204','2205','2206','2301','2302','2303','2304','2305','2306','2401','2402','2403','2404','2405','2406','2601','2606','2701','2702','2703','2704','2706','2801','2803','2805','2806','2901','2902','2903','2904','2905','2906','3001','3002','3003','3004','3005','3006','3101','3102','3103','3104','3105','3106','3201','3202','3203','3204','3205','3206','3301','3302','3303','3304','3305','3306','3401','3402','3403','3404','3405','3406','3501','3502','3503','3504','3505','3506','3601','3602','3603','3604','3605','3606','3701','3702','3703','3704','3705','3706','3801','3802','3803','3804','3805','3806','3901','3902','3903','3904','3905','3906','4001','4002','4003','4004','4005','4006','4101','4102','4103','4104','4105','4106','4201','4202','4203','4204','4205','4206','4301','4302','4303','4304','4305','4306','4401','4402','4403','4404','4406','4501','4502','4503','4504','4506','4601','4602','4603','4604','4606','4701','4702','4703','4704','4706','4801','4802','4803','4804','4806','4901','4902','4903','4904','4906','5001','5002','5003','5004','5006','5101','5102','5103','5104','5106','5201','5202','5203','5204','5206','5301','5302','5303','5304','5306','5401','5402','5403','5404','5406','5501','5502','5503','5504','5506','5601','5602','5603','5604','5606','5701','5702','5703','5801','5802','5803','5901','5902','5903','6001','6002','6003','61E','61W','62E','62W'),
            '111P' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '813', '814', '815', '816', '817', '818', '819', '820', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '920', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019', '1020', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1113', '1114', '1115', '1116', '1117', '1118', '1119', '1120'),
            '909W' =>  array('401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','916','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014'),
            'TEST' =>  array('2A','2B','2C','2D','2E','3A','3B','3C','3D','3E','4A','4B','4C','4D','4E','5A','5B','5C','5D','5E','601','602','607','608','701','702','703','704','705','706','707','801','802','803','804','805','806','807','901','902','903','904','905','906','907','1001','1002','1003','1004','1005','1006','1007','1101','1102','1103','1104','1105','1106','1107','1201','1202','1203','1204','1205','1206','1207','1301','1302','1303','1304','1305','1306','1307','1401','1402','1403','1404','1405','1406','1407','1501','1502','1503','1504','1505','1506','1507','1601','1602','1603','1604','1605','1606','1607','1701','1702','1703','1704','1705','1706','1707','1801','1802','1803','1804','1805','1806','1807','1901','1902','1903','1904','1905','1906','1907','2001','2002','2003','2004','2005','2006','2007','2101','2102','2103','2104','2105','2106','2107','2201','2202','2203','2204','2205','2206','2207','2301','2302','2303','2304','2305','2306','2307','2401','2402','2403','2404','2405','2406','2407','2501','2502','2503','2504','2505','2506','2507','2601','2602','2603','2604','2605','2606','2607','2701','2702','2703','2704','2705','2706','2707','2801','2802','2803','2804','2805','2806','2807','2901','2902','2903','2904','2905','2906','2907','3001','3002','3003','3004','3005','3006','3007','3101','3102','3103','3104','3105','3106','3107','3201','3202','3203','3204','3205','3206','3207','3301','3302','3303','3304','3305','3401','3402','3403','3404','3405','3501','3502','3504','3503'),
            '1600I' =>  array('501','502','503','504','505','506','507','508','509','510','601','602','603','604','605','606','607','608','609','610','701','702','703','704','705','706','707','708','709','710','801','802','803','804','805','806','807','808','809','810','901','902','903','904','905','906','907','908','909','910','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1901','1902','1903'),
            '1211P' =>  array('301', '305', '306', '401', '405', '406', '601', '602', '603', '604', '605', '606', '701', '702', '703', '704', '705', '706', '801', '802', '803', '804', '805', '806', '901', '902', '903', '904', '905', '906', '1001', '1002', '1003', '1004', '1005', '1006', '1101', '1102', '1103', '1104', '1105', '1106', '1201', '1202', '1203', '1204', '1205', '1206', '1301', '1302', '1303', '1304', '1305', '1306', '1401', '1402', '1403', '1404', '1405', '1406', '1501', '1502', '1503', '1504', '1505', '1506', '1601', '1602', '1603', '1604', '1605', '1606', '1701', '1702', '1703', '1704', '1705', '1706', '1801', '1802', '1803', '1804', '1805', '1806', '1901', '1902', '1903', '1904', '1905', '1906', '2001', '2002', '2003', '2004', '2005', '2006', '2101', '2102', '2103', '2104', '2105', '2106', '2201', '2202', '2203', '2204', '2205', '2206', '2301', '2302', '2303', '2304', '2305', '2306', '2401', '2402', '2403', '2404', '2405', '2406', '2501', '2502', '2503', '2504', '2505', '2506', '2601', '2602', '2603', '2604', '2605', '2606', '2701', '2702', '2703', '2704', '2705', '2706', '2801', '2802', '2803', '2804', '2805', '2806', '2901', '2902', '2903', '2904', '2905', '2906', '3001', '3002', '3003', '3004', '3005', '3006', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3202', '3203', '3204', '3205', '3206', '3301', '3302', '3303', '3304', '3305', '3306', '3401', '3402', '3403', '3404', '3405', '3406', '3501', '3502', '3505', '3506', '3601', '3602', '3603', '3605', '3606', '3701', '3702', '3703', '3705', '3706', '3801', '3802', '3803', '3805', '3806', '3901', '3902', '3903', '3905', '3906', '4001', '4002', '4003', '4005', '4006', '4101', '4102', '4103', '4105', '4106', '4201', '4202', '4203', '4205', '4206', '4301', '4302', '4303', '4305', '4306', '4401', '4402', '4403', '4405', '4406', '4501', '4502', '4503', '4505', '4506', '4601', '4602', '4603', '4605', '4701', '4702', '4703', '4705', '4801', '4802', '4803', '4805', '4901', '4902', '4903', '4905', '5001', '5002', '5005', '5101', '5102', '5105', '5201', '5202', '5205', '5301', '5302', '5305', '5401', '5402', '5405', '5501', '5502', '5505', '5601', '5602', '5605', '5701', '5702', '5705', '5801', '5802', '5805', '5901', '5902', '5905', '6001', '6002', '6005', '6101', '6102', '6105', '6201', '6202', '6205'),
            '1524S' =>  array('1524 S Sangamon Street' => array('301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '801', '802', '803', '804', '805', '806', '807', '808')),
            '400O' =>  array('501', '502', '503', '504', '505', '506', '507', '508', '508B', '509', '510', '511', '512', '513', '601', '602', '603', '604', '605', '606', '607', '608', '608B', '609', '610', '611', '612', '613', '701', '702', '703', '704', '705', '706', '707', '708', '708B', '709', '710', '711', '712', '713', '801', '802', '803', '804', '805', '806', '807', '808', '901', '902', '903', '904', '905', '906', '907', '908', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1201', '1203', '1204', '1205', '1206', '1207', '1208', '1301', '1303', '1304', '1305', '1306', '1307', '1308', '1401', '1403', '1404', '1405', '1406', '1407', '1408', '1501', '1503', '1504', '1505', '1506', '1507', '1508', '1601', '1603', '1604', '1605', '1606', '1607', '1608', '1701', '1703', '1704', '1705', '1706', '1707', '1708', '1801', '508B', '608B', '708B'),
            '125E13' =>  array( '501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1113','1114','1115','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1213','1214','1215','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1313','1314','1315','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1412','1501','1512'),
            '111M' =>  array( '701','702','703','704','705','706','707','708','709','710','711','712','801','802','803','804','805','806','807','808','809','810','811','812','901','902','903','904','905','906','907','908','909','910','911','912','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1412','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1511','1512','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1611','1612','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1711','1712','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1812','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','1911','1912','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2111','2112','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2211','2212','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2311','2312','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2411','2412','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2511','2512','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2611','2612','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2711','2712','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2811','2812','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','2911','2912','3001','3002','3003','3004','3005','3006','3007','3008','3009','3010','3011','3012','3101','3102','3103','3104','3105','3106','3107','3108','3109','3110','3111','3112','3201','3202','3203','3204','3205','3206','3207','3208','3209','3210','3211','3212','3301','3302','3303','3304','3305','3306','3307','3308','3309','3310','3311','3312','3401','3402','3403','3404','3405','3406','3407','3408','3409','3410','3411','3412'),
            '1600W' =>  array('2E','2W','3E','3W','4E','4W','5E','5W','6E','6W'),
            '850C' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '813', '814', '815', '816', '817', '818', '819', '820', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '920', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019', '1020', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1113', '1114', '1115', '1116', '1117', '1118', '1119', '1120'),
            'UC' =>  array('1033 W 14th Place' => array( '110', '111', '112', '113', '114', '115', '116', '117', '118', '119', '130', '131', '132', '133', '134', '135', '136', '137', '138', '139', '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '221', '222', '234', '235', '236', '237', '238', '239', '240', '241', '242', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '321', '322', '334', '335', '336', '337', '338', '339', '340', '341', '342', '407', '408', '409', '410', '411', '412', '413', '414')),
            '400X' =>  array('N Clinton Street' => array('401', '402', '405', '406', '407', '409', '410', '416', '417', '421', '425', '428', '429', '433', '434', '437', '440', '441', '445', '446', '449', '452', '453', '457', '458', '460', '461', '462', '465', '468', '469', '473', '474', '477', '480', '481', '485', '486', '492', '496')),
            '1201P' =>  array('201','202','301','302','303','304','401','402','403','404','601','602','603','604','605','606','701','702','703','704','705','706','801','802','803','804','805','806','901','902','903','904','905','906','1001','1002','1003','1004','1005','1006','1101','1102','1103','1104','1105','1106','1201','1202','1203','1204','1205','1206','1301','1302','1303','1304','1305','1306','1401','1402','1403','1404','1405','1406','1501','1502','1503','1504','1505','1506','1601','1602','1603','1604','1605','1606','1701','1702','1703','1704','1705','1706','1801','1802','1803','1804','1805','1806','1901','1902','1903','1904','1905','1906','2001','2002','2003','2004','2005','2006','2101','2102','2103','2104','2105','2106','2201','2202','2203','2204','2205','2206','2301','2302','2303','2304','2305','2306','2401','2402','2403','2404','2405','2406','2501','2502','2503','2504','2505','2506','2601','2602','2603','2604','2605','2606','2701','2702','2703','2704','2705','2706','2801','2802','2803','2804','2805','2806','2901','2902','2903','2904','2905','2906','3001','3002','3003','3004','3005','3006','3101','3102','3103','3104','3105','3106','3201','3202','3203','3204','3205','3206','3301','3302','3303','3304','3305','3306','3401','3402','3403','3404','3405','3406','3501','3502','3503','3504','3505','3506','3601','3602','3603','3604','3605','3606','3701','3702','3703','3704','3705','3706','3801','3802','3803','3804','3805','3806','3901','3902','3903','3904','3905','3906','4001','4002','4003','4004','4005','4006','4101','4102','4103','4104','4105','4106','4201','4202','4203','4204','4205','4206','4301','4302','4303','4304','4305','4306','4401','4402','4403','4404','4405','4406','4501','4502','4503','4504','4505','4506','4601','4602','4603','4604','4605','4606','4701','4702','4703','4704','4705','4706','4801','4802','4803','4804','4805','4806','4901','4902','4903','4904','4905','4906','5001','5002','5003','5004','5005','5006','5101','5102','5103','5104','5105','5106','5201','5202','5203','5204','5205','5206','5301','5302','5303','5304','5305','5306'),
            '14P' =>  array('102','103','2A','2B','2C','2D','2E','2FGH','3A','3B','3C','3D','3E','3F','3G','3H','4A','4B','4C','4D','4E','4F','4G','4H','5A','5B','5C','5D','5E','5F','5G','5H','67H','6A','6B','6C','6D','6E','6F','6G','7AB','7C','7D','7E','7FG','8A'),
            '1250I' =>  array('101','102','103','104','105','106','107','108','109','110','111','201','203','204','205','206','208','209','210','211','301','302','303','304','305','306','307','308','309','310','311','401','402','403','404','405','406','407','408','409','410','411','501','502','503','504','505','506','507','508','509','510','511','601','602','603','604','605','606','607','608','609','610','611','701','702','703','704','705','706','707','708','709','710','711','712','713','714','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','916','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1016','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1113','1114','1115','1116','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1301','1303','1304','1305','1306','1307','1308','1309','1401','1405','1406','1407','1408','1409','P-110','P-126'),
            '1335P' =>  array( '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1310', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010'),
            '1339D' =>  array('2A', '2B', '2C', '2D', '2E', '2F', '2G', '2H', '3A', '3B', '3C', '3D', '3E', '3F', '3G', '3H', '4A', '4B', '4C', '4D', '4E', '4F', '4G', '4H', '5A', '5B', '5C', '5D', '5E', '5F', '5G', '5H', '6A', '6B', '6C', '6D', '6E', '6F', '6G', '6H', '7A', '7B', '7C', '7D', '7E', '7F', '7G', '7H', '8A', '8B', '8C', '8D', '8E', '8F', '8G', '8H', '9A', '9B', '9C', '9D', '9E', '9F', '9G', '9H', '10A', '10B', '10C', '10D', '10E', '10F', '10G', '10H', '11A', '11B', '11C', '11D', '11E', '11F', '11G', '11H', '12A', '12B', '12C', '12D', '12E', '12F', '12G', '12H', '14A', '14B', '14C', '14D', '14E', '14F', '14G', '14H', '15A', '15B', '15C', '15D', '15E', '15F', '15G', '15H', '16A', '16B', '16C', '16D', '16E', '16F', '16G', '16H'),
            '657F' =>  array('201','202','203','204','205','206','207','208','209','210','211','212','213','301','302','303','304','305','306','307','308','309','310','311','312','313','401','402','403','404','405','406','407','408','409','410','411','412','413','501','502','503','504','505','506','507','508','509','510','511','512','513','601','602','603','604','605','606','607','608','609','610','611','612','613','701','702','703','704','705','706','707','708','709','710','711','712'),
            '565Q' =>  array(  '501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','517','518','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','617','618','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','717','718','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','817','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','916','917','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1016','1017','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1113','1114','1115','1116','1117','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1213','1214','1215','1216','1217','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1313','1314','1315','1316','1317','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1412','1413','1414','1415','1416','1417','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1511','1512','1513','1514','1515','1516','1517','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1611','1612','1613','1614','1615','1616','1617','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1711','1712','1713','1714','1715','1716','1717','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1812','1813','1814','1815','1816','1817'),
            '610M' =>  array('Event Space'),
            '60M' =>  array('1501','1502','1503','1504','1507','1508','1601','1602','1603','1604','1606','1607','1608','1701','1702','1703','1704','1705','1706','1707','1708','1801','1802','1803','1804','1805','1806','1807','1808','1901','1902','1903','1904','1905','1906','1907','1908','2001','2002','2003','2004','2005','2006','2007','2008','2101','2102','2103','2104','2105','2106','2107','2108','2201','2202','2203','2204','2205','2206','2207','2208','2301','2302','2303','2304','2305','2306','2307','2308','2401','2402','2403','2404','2405','2406','2407','2408','2501','2502','2503','2504','2505','2506','2507','2508','2601','2602','2603','2604','2605','2606','2607','2608','2701','2702','2703','2704','2705','2706','2707','2708','2801','2802','2803','2804','2805','2806','2807','2808','2901','2902','2903','2904','2905','2906','2907','2908','3001','3002','3003','3004','3005','3006','3007','3008','3101','3102','3103','3104','3105','3106','3107','3108','3201','3202','3203','3204','3205','3206','3207','3208','3301','3302','3303','3304','3305','3306','3307','3308','3401','3402','3403','3404','3405','3406','3407','3408','3501','3502','3503','3504','3505','3506','3507','3508','3601','3602','3603','3604','3605','3606','3607','3608','3701','3702','3703','3704','3705','3706','3707','3708','3801','3802','3803','3804','3805','3806','3807','3808','3901','3902','3903','3904','3905','3906','3907','3908','4001','4003','4004','4005','4006','4007','4008','4101','4102','4103','4108','4201','4202','4203','4204','4205','4301','4302','4303','4304','4305','4401','4402','4403','4404','4405','4406','4501','4502','4503','4504','4505','4506','4601','4602','4603','4604','4605','4606','4701','4702','4703','4704','4705','4706','4801','4802','4803','4804','4805','4806','4901','4902','4903','4904','4905','4906','5001','5002','5003','5004','5005','5006','5101','5102','5103','5104','5105','5106','5201','5202','5203','5204','5205','5206','5301','5302','5303','5304','5305','5306','5401','5402','5403','5404','5405','5406','5501','5502','5503','5504','5505','5506','5601','5602','5603','5604','5605','5606','5701','5702','5703','5704','5705','5706','5801','5802','5803','5804','5805','5806','5901','5902','5903','5904','5905','5906','6001','6002','6004','6101','6102','6103','6104','6201','6202','6203','6204','6301','6302','6303','6304','6401','6402','6403','6404','6501','6502','6503','6504','6601','6602','6603','6604','6701','6702','6703','6704','6801','6802','6803','6804','6901','6902','7001','7002','7101','7102','7201'),
            '1910I' =>  array('1910 South Indiana Avenue' => array('101','102','103','104','105','106','107','108','114','115','116','117','118','119','120','121','122','123','124','201','202','203','204','205','206','207','209','210','214','215','216','217','218','219','220','221','222','223','224','225','226','301','302','303','304','305','306','311','312','313','314','315','316','317','318','319','320','321','322','323','324','325','326','401','402','403','404','405','406','414','415','416','417','418','419','420','421','422','423','424','425','426','501','502','503','504','505','506','514','515','516','517','518','519','520','521','522','523','524','525','526','614','615','616','617','618','619','620','621','622','623','624','625','626','627','714','715','720','721','722','727')),
            '8R' =>  array('1001','1002','1003','1004','1005','1006','1007','1008','1101','1102','1103','1104','1105','1106','1107','1108','1201','1202','1203','1204','1205','1206','1207','1208','1401','1402','1403','1404','1405','1406','1407','1408','1501','1502','1503','1504','1505','1506','1507','1508','1601','1602','1603','1604','1605','1606','1607','1608','1701','1702','1703','1704','1705','1706','1707','1708','1801','1802','1803','1804','1805','1806','1807','1808','1901','1902','1903','1904','1905','1906','1907','1908','2001','2002','2003','2004','2005','2006','2007','2008','2101','2102','2103','2104','2105','2106','2107','2108','2201','2202','2203','2204','2205','2206','2207','2208','2301','2302','2303','2304','2305','2306','2307','2308','2401','2402','2403','2404','2405','2406','2407','2408','2501','2502','2503','2504','2505','2506','2507','2508','2601','2602','2603','2604','2605','2606','2607','2608','2701','2702','2703','2704','2705','2706','2707','2708','2801','2802','2803','2804','2805','2806','2807','2808','2901','2902','2903','2904','2905','2906','2907','2908','3001','3002','3003','3004','3005','3006','3007','3008','3101','3102','3103','3104','3105','3106','3107','3108','3201','3202','3203','3204','3205','3206','3207','3208','3301','3302','3303','3304','3305','3306','3307','3308'),
            '701W' =>  array( '801', '802', '803', '804', '901', '902', '903', '903', '904', '905', '906', '907', '908', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1801', '1802', '1803', '1804', '1805', '1805', '1806', '1807', '1808', '1901', '1902', '1903', '1904', '1905', '1905', '1906', '1907', '1908', '2001', '2002', '2003', '2004', '2005', '2006', '2101', '2102', '2103', '2104', '2105', '2106', '2201', '2202', '2203', '2204', '2205', '2206', '2301', '2302', '2303', '2304', '2305', '2306', '2401', '2402', '2403', '2404', '2405', '2406', '2406', '2501', '2502', '2503', '2504', '2505', '2506', '2601', '2602', '2602', '2603', '2603', '2604', '2605', '2606', '2701', '2702', '2703', '2704', '2705', '2706', '2801', '2802', '2803', '2804', '2805', '2805', '2806', '2806', '2901', '2902', '2903', '2904', '2904', '2905', '2906', '3001', '3002', '3003', '3004', '3005', '3006', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3202', '3203', '3204', '3205', '3206', '3301', '3301', '3302', '3302', '3303', '3304', '3305', '3306', '3400', '3400', '3401', '3401'),
            '1300S' =>  array( '101', '102', '201', '203', '204', '301', '302', '303', '304', '401', '402', '403', '404', '501', '502', '503', '504', '601', '602', '603', '604', '701', '702', '703', '801', '802', '803', '901', '902', '903', '1001', '1002', '1003', '1101', '1102', '1201', '1202' ),
            '2323P' =>  array('101','102','103','104','105','106','107','108','109','111','112','113','114','116','118','120','123','124','125','128','129','130','131','132','135','136','137','138','139','140','201','202','203','204','205','206','207','208','209','211','212','213','214','216','217','218','219','220','221','222','224','225','226','227','228','229','230','232','233','234','235','236','237','238','240','301','302','303','304','305','306','307','308','309','311','312','313','314','316','317','318','319','320','321','322','324','325','326','327','328','329','330','332','333','334','335','336','337','338','340','401','402','403','404','405','406','407','408','409','411','412','413','414','416','417','418','419','420','421','422','501','502','503','504','505','506','507','508','509','511','512','513','514','516','517','518','519','520','521','522','601','602','603','604','605','606','607','608','609','611','612','613','614','616','617','618','619','620','621','622'),
            '1635B' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '213', '214', '215', '216', '217', '218', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '321', '322', '323', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '421', '422', '423', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '521', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714'),
            '71H' =>  array('201', '202', '203', '204', '205', '206', '301', '302', '401', '402', '403', '404', '405', '406', '501', '502', '503', '504', '505', '506', '601', '602', '603', '604', '605', '606', '701', '702', '703', '704', '705', '706', '801', '802', '803', '804', '805', '806', '901', '902', '903', '904', '905', '906', '1001', '1002', '1003', '1004', '1005', '1006', '1101', '1102', '1103', '1104', '1105', '1106', '1201', '1202', '1202', '1203', '1204', '1205', '1206', '1301', '1302', '1303', '1304', '1305', '1306', '1401', '1402', '1403', '1404', '1405', '1406', '1501', '1502', '1503', '1504', '1505', '1506', '1601', '1602', '1603', '1604', '1605', '1606', '1701', '1702', '1703', '1704', '1705', '1706', '1801', '1802', '1803', '1804', '1805', '1806', '1901', '1902', '1903', '1904', '1905', '1906', '2001', '2002', '2003', '2004', '2005', '2006', '2101', '2102', '2103', '2104', '2105', '2106', '2201', '2202', '2203', '2204', '2205', '2206', '2301', '2302', '2303', '2304', '2305', '2306', '2401', '2402', '2403', '2404', '2405', '2406', '2501', '2502', '2503', '2504', '2505', '2506', '2511', '2601', '2602', '2603', '2604', '2605', '2606', '2701', '2702', '2703', '2704', '2705', '2706', '2801', '2802', '2803', '2804', '2805', '2806', '2901', '2902', '2903', '2904', '2905', '2906', '3001', '3002', '3003', '3004', '3005', '3006', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3202', '3203', '3204', '3205', '3206', '3301', '3302', '3303', '3304', '3305', '3306', '3401', '3402', '3403', '3405', '3406', '3501', '3502', '3503', '3505', '3506', '3601', '3602', '3603', '3604', '3605', '3606', '3701', '3702', '3703', '3704', '3705', '3706', '3801', '3802', '3803', '3804', '3805', '3806', '3901', '3902', '3903', '3904', '3905', '3906', '4001', '4002', '4003', '4005', '4006', '4101', '4102', '4103', '4104', '4105', '4106', '4201', '4202', '4203', '4204', '4205', '4206', '4301', '4302', '4303', '4304', '4305', '4306', '4401', '4402', '4403', '4404', '4405', '4406', '4501', '4502', '4503', '4504', '4505', '4506', '4601', '4602', '4603', '4604', '4605', '4606', '4701', '4702'),
            '730C' =>  array('203', '204', '205', '206', '207', '208', '301', '302', '303', '304', '305', '306', '307', '308', '311', '312', '401', '402', '403', '404', '405', '406', '407', '408', '411', '412', '501', '502', '503', '504', '505', '506', '507', '508', '511', '512', '601', '602', '603', '604', '605', '606', '607', '608', '611', '612', '701', '702', '703', '704', '705', '706', '707', '708', '711', '712', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '901', '902', '903', '904','905', '906', '907', '908', '909', '910', '911', '912', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1211', '1212', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1310', '1311', '1312', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1411', '1412', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1511', '1512', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1611', '1612', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1711', '1712', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '1911', '1912', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2110', '2111', '2112', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2210', '2211', '2212', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2309', '2310', '2311', '2312', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2409', '2410', '2411', '2412', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2509', '2510', '2511', '2512', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2609', '2610', '2611', '2612', '2701', '2703', '2704', '2706', '2707', '2709', '2710', '2712', '2801', '2803', '2804', '2806', '2807', '2809', '2810', '2812'),
            '737W' =>  array('601','602','604','605','606','607','608','609','610','701','702','704','705','706','707','708','709','710','801','802','804','805','806','807','808','809','810','901','902','904','905','906','907','908','909','910','1001','1003','1004','1005','1006','1007','1008','1009','1010','1101','1104','1105','1106','1107','1108','1109','1110','1201','1203','1204','1205','1206','1207','1208','1209','1210','1301','1304','1305','1306','1307','1308','1309','1310','1401','1402','1403','1404','1407','1408','1410','1501','1502','1504','1506','1507','1508','1510','1601','1602','1603','1604','1607','1608','1610','1701','1702','1704','1706','1707','1708','1710','1801','1802','1803','1804','1806','1807','1808','1810','1901','1902','1904','1906','1907','1908','1910','2001','2002','2003','2004','2006','2007','2008','2010','2101','2104','2106','2107','2110','2201','2203','2204','2207','2208','2210','2301','2302','2304','2307','2308','2310','2401','2402','2403','2404','2407','2408','2410','2501','2502','2504','2506','2507','2508','2510','2601','2602','2603','2604','2607','2608','2610','2701','2702','2704','2707','2708','2710','2801','2802','2803','2804','2806','2807','2808','2810','2901','2902','2904','2907','2908','2910','3001','3002','3003','3004','3006','3007','3008','3010','3101','3102','3104','3106','3107','3108','3110','3201','3202','3203','3204','3207','3208','3210','3301','3302','3304','3306','3307','3308','3310','3401','3404','3406','3407','3408','3410','3506','3507','3508','3510','3607','3608','3701','3707','3708'),
            '235V' =>  array('1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1411', '1412', '1413', '1414', '1415', '1416', '1417', '1418', '1419', '1420', '1421', '1422', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1511', '1512', '1513', '1514', '1515', '1516', '1517', '1518', '1519', '1520', '1521', '1522', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1611', '1612', '1613', '1614', '1615', '1616', '1617', '1618', '1619', '1620', '1621', '1622', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1711', '1712', '1713', '1714', '1715', '1716', '1717', '1718', '1719', '1720', '1721', '1722', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812', '1813', '1814', '1815', '1816', '1817', '1818', '1819', '1820', '1821', '1822', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '1911', '1912', '1913', '1914', '1915', '1916', '1917', '1918', '1919', '1920', '1921', '1922', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2110', '2111', '2112', '2113', '2114', '2115', '2116', '2117', '2118', '2119', '2120', '2121', '2122', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2210', '2211', '2212', '2213', '2214', '2215', '2216', '2217', '2218', '2219', '2220', '2221', '2222', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2309', '2310', '2311', '2312', '2313', '2314', '2315', '2316', '2317', '2318', '2319', '2320', '2321', '2322', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2409', '2410', '2411', '2412', '2413', '2414', '2415', '2416', '2417', '2418', '2419', '2420', '2421', '2422', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2509', '2510', '2511', '2512', '2513', '2514', '2515', '2516', '2517', '2518', '2519', '2520', '2521', '2522', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2609', '2610', '2611', '2612', '2613', '2614', '2615', '2616', '2617', '2618', '2619', '2620', '2621', '2622', '2701', '2702', '2703', '2704', '2705', '2706', '2707', '2708', '2709', '2710', '2711', '2712', '2713', '2714', '2715', '2716', '2717', '2718', '2719', '2720', '2721', '2722', '2801', '2802', '2803', '2804', '2805', '2806', '2807', '2808', '2809', '2810', '2811', '2812', '2813', '2814', '2815', '2816', '2817', '2818', '2819', '2820', '2821', '2822', '2901', '2902', '2903', '2904', '2905', '2906', '2907', '2908', '2909', '2910', '2911', '2912', '2913', '2914', '2915', '2916', '2917', '2918', '2919', '2920', '2921', '2922', '3001', '3002', '3003', '3004', '3005', '3006', '3007', '3008', '3009', '3010', '3011', '3012', '3013', '3014', '3015', '3016', '3017', '3018', '3019', '3020', '3021', '3022', '3101', '3102', '3103', '3104', '3105', '3106', '3107', '3108', '3109', '3110', '3111', '3112', '3113', '3114', '3115', '3116', '3117', '3118', '3119', '3120', '3121', '3122', '3201', '3202', '3203', '3204', '3205', '3206', '3207', '3208', '3209', '3210', '3211', '3212', '3213', '3214', '3215', '3216', '3217', '3218', '3219', '3220', '3221', '3222', '3301', '3302', '3303', '3304', '3305', '3306', '3307', '3308', '3309', '3310', '3311', '3312', '3313', '3314', '3315', '3316', '3317', '3318', '3319', '3320', '3321', '3322', '3401', '3402', '3403', '3404', '3405', '3406', '3407', '3408', '3409', '3410', '3411', '3412', '3413', '3414', '3415', '3416', '3417', '3418', '3419', '3420', '3421', '3422', '3501', '3502', '3503', '3504', '3505', '3506', '3507', '3508', '3509', '3510', '3511', '3512', '3513', '3514', '3515', '3516', '3517', '3518', '3519', '3520', '3521', '3522', '3601', '3602', '3603', '3604', '3605', '3606', '3607', '3608', '3609', '3610', '3611', '3612', '3613', '3614', '3615', '3616', '3617', '3618', '3619', '3620', '3621', '3622', '3701', '3702', '3703', '3704', '3705', '3706', '3707', '3708', '3709', '3710', '3711', '3712', '3713', '3714', '3715', '3716', '3717', '3718', '3719', '3720', '3721', '3722', '3801', '3802', '3803', '3804', '3805', '3806', '3807', '3808', '3809', '3810', '3811', '3812', '3813', '3814', '3815', '3816', '3817', '3818', '3819', '3820', '3821', '3822', '3901', '3902', '3903', '3904', '3905', '3906', '3907', '3908', '3909', '3910', '3911', '3912', '3913', '3914', '3915', '3916', '3917', '3918', '3919', '3920', '3921', '3922', '4001', '4002', '4003', '4004', '4005', '4006', '4007', '4008', '4009', '4010', '4011', '4012', '4013', '4014', '4015', '4016', '4017', '4018', '4019', '4020', '4021', '4022', '4101', '4102', '4103', '4104', '4105', '4106', '4107', '4108', '4109', '4110', '4111', '4112', '4113', '4114', '4115', '4116', '4117', '4118', '4119', '4120', '4121', '4122', '4201', '4202', '4203', '4204', '4205', '4206', '4207', '4208', '4209', '4210', '4211', '4212', '4213', '4214', '4215', '4216', '4217', '4218', '4219', '4220', '4221', '4222', '4301', '4302', '4303', '4304', '4305', '4306', '4307', '4308', '4309', '4310', '4311', '4312', '4313', '4314', '4315', '4316', '4317', '4318', '4319', '4320', '4321', '4322', '4401', '4402', '4403', '4404', '4405', '4406', '4407', '4408', '4409', '4410', '4411', '4412', '4413', '4414', '4415', '4416', '4417', '4418', '4419', '4420', '4421', '4422', '4501', '4502', '4503', '4504', '4505', '4506', '4507', '4508', '4509', '4510', '4511', '4512', '4513', '4514', '4515', '4516', '4601', '4602', '4603', '4604', '4605', '4606', '4607', '4608', '4609', '4610', '4611', '4612', '4613', '4614', '4615', '4616'),
            '200G' =>  array('601','603','604','605','606','701','702','703','704','705','706','801','802','803','804','805','806','901','902','903','904','905','906','1001','1002','1003','1004','1005','1006','1101','1102','1103','1104','1105','1106','1201','1202','1203','1204','1205','1206','1301','1302','1303','1304','1305','1306','1401','1402','1403','1404','1405','1406','1501','1502','1503','1504','1505','1506','1601','1602','1603','1604','1605','1606','1701','1702','1703','1704','1705','1706','1801','1802','1803','1804','1805','1806','1901','1903','1904','1905','1906','2001','2002','2003','2004','2005','2006','2101','2102','2103','2104','2105','2106','2202','2203','2204','2301','2303','2304','2401','2501','2502','2601','2602','2701'),
            '520H' =>  array('200', '201', '202', '203', '204', '205', '206', '207', '208', '210', '211', '212', '213', '214', '215', '216', '218', '300', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '318', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '418', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '600', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '618'),
            '222C' =>  array('301','302','303','304','305','306','307','308','309','310','311','312','401','402','403','404','405','406','407','408','409','410','411','412','501','502','503','504','505','506','507','508','509','510','511','512','601','602','603','604','605','606','607','608','609','610','611','612','701','702','703','704','705','706','707','708','709','710','711','712','801','802','803','804','805','806','807','808','809','810','811','812','901','902','903','904','905','906','907','908','909','910','911','912','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','3001','3002','3003','3004','3005','3006','3007','3008','3009','3010','3101','3102','3103','3104','3105','3106','3107','3108','3109','3110','3201','3202','3203','3204','3205','3206','3207','3208','3209','3210','3301','3302','3303','3304','3305','3306','3307','3308','3309','3310','3401','3402','3403','3404','3405','3406','3407','3408','3409','3410','3501','3502','3503','3504','3505','3506','3507','3508','3509','3510','3601','3602','3603','3604','3605','3606','3607','3608','3609','3610','3701','3702','3703','3704','3705','3706','3707','3708','3709','3710','3801','3802','3803','3804','3805','3806','3807','3808','3809','3810','3901','3902','3903','3904','3905','3906','3907','3908','3909','3910','4001','4002','4003','4004','4005','4006','4007','4008','4009','4010','4101','4102','4103','4104','4105','4106','4107','4108','4109','4110','4201','4202','4203','4204','4205','4206','4207','4208','4209','4210','4301','4302','4303','4304','4305','4306','4307','4308','4309','4310','4401','4402','4403','4404','4405','4406','4407','4408','4409','4410','4501','4502','4503','4504','4505','4506','4507','4508','4509','4510','4601','4602','4603','4604','4605','4606','4607','4608','4609','4610','4701','4702','4703','4801','4802','4803','4901','4902','4903','5001','5002','5003','5101','5102','5103','5201','5202','5203'),
            '345C' =>  array('301','302','303','304','305','306','307','308','401','402','403','404','405','406','407','408','501','502','503','504','505','506','507','508','601','602','603','604','605','606','607','608','701','702','703','704','705','706','707','708','801','802','803','804','805','806','807','808','901','902','903','904','905','906','907','908','1001','1002','1003','1004','1005','1006','1007','1008','1101','1102','1103','1104','1105','1106','1107','1108','1201','1202','1203','1204','1205','1206','1207','1208','1301','1302','1303','1304','1305','1306','1307','1308','1401','1402','1403','1404','1405','1406','1407','1408','1501','1502','1503','1504','1505','1506','1507','1508','1601','1602','1603','1604','1605','1606','1607','1608'),
            '711D' =>  array('711 S Dearborn St' => array('201', '202', '203', '204', '205', '206', '207', '208', '211', '212E', '212W', '301', '302', '303', '304', '305', '306', '307', '308', '311', '312', '401', '402', '403', '404', '405', '406', '407', '410', '411', '412', '501', '502', '503', '504', '505', '506', '507', '508', '510', '511', '512', '601', '602', '604', '605', '606', '607', '608', '610', '611', '612', '613', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '800', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '910', '911', '912', '913', '1010', '1011', '1012', '212E', '212W', '701C', '703C', '705C', '707B', '707C', '715C', '717C', '719C', '720C', '721C', '723C', '725C', '729C', '731C', '733C', '701BA', '701BD', '701BF', '733BA', '733BC')),
            '800C' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '813', '814', '815', '816', '817', '818', '819', '820', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '920', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019', '1020', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1113', '1114', '1115', '1116', '1117', '1118', '1119', '1120'),
            '505M' =>  array('201', '202', '203', '204', '205', '206', '301', '302', '401', '402', '403', '404', '405', '406', '501', '502', '503', '504', '505', '506', '601', '602', '603', '604', '605', '606', '701', '702', '703', '704', '705', '706', '801', '802', '803', '804', '805', '806', '901', '902', '903', '904', '905', '906', '1001', '1002', '1003', '1004', '1005', '1006', '1101', '1102', '1103', '1104', '1105', '1106', '1201', '1202', '1202', '1203', '1204', '1205', '1206', '1301', '1302', '1303', '1304', '1305', '1306', '1401', '1402', '1403', '1404', '1405', '1406', '1501', '1502', '1503', '1504', '1505', '1506', '1601', '1602', '1603', '1604', '1605', '1606', '1701', '1702', '1703', '1704', '1705', '1706', '1801', '1802', '1803', '1804', '1805', '1806', '1901', '1902', '1903', '1904', '1905', '1906', '2001', '2002', '2003', '2004', '2005', '2006', '2101', '2102', '2103', '2104', '2105', '2106', '2201', '2202', '2203', '2204', '2205', '2206', '2301', '2302', '2303', '2304', '2305', '2306', '2401', '2402', '2403', '2404', '2405', '2406', '2501', '2502', '2503', '2504', '2505', '2506', '2601', '2602', '2603', '2604', '2605', '2606', '2701', '2702', '2703', '2704', '2705', '2706', '2801', '2802', '2803', '2804', '2805', '2806', '2901', '2902', '2903', '2904', '2905', '2906', '3001', '3002', '3003', '3004', '3005', '3006', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3202', '3203', '3204', '3205', '3206', '3301', '3302', '3303', '3304', '3305', '3306', '3401', '3402', '3403', '3405', '3406', '3501', '3502', '3503', '3505', '3506', '3601', '3602', '3603', '3604', '3605', '3606', '3701', '3702', '3703', '3704', '3705', '3706', '3801', '3802', '3803', '3804', '3805', '3806', '3901', '3902', '3903', '3904', '3905', '3906', '4001', '4002', '4003', '4005', '4006', '4101', '4102', '4103', '4104', '4105', '4106', '4201', '4202', '4203', '4204', '4205', '4206', '4301', '4302', '4303', '4304', '4305', '4306', '4401', '4402', '4403', '4404', '4405', '4406', '4501', '4502', '4503', '4504', '4505', '4506', '4601', '4602', '4603', '4604', '4605', '4606', '4701', '4702'),
            '400C' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '301', '302', '303', '304', '305', '306', '307', '308', '309', '501', '502', '503', '504', '505', '506', '507', '508', '509', '601', '602', '603', '604', '605', '606', '607', '608', '609', '701', '702', '703', '704', '705', '706', '707', '708', '709', '401', '402', '403', '404', '405', '406', '407', '408', '409'),
            '125J' =>  array('601','602','603','604','605','606','607','608','609','610','701','702','703','704','705','706','707','708','709','710','801','802','803','804','805','806','807','808A','808B','809','810','901','902','903','904','905','906','907','908','909','910','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','3001','3002','3003','3004','3005','3006','3007','3008','3009','3010','3101','3102','3103','3104','3105','3106','3107','3108','3109','3110','3201','3202','3203','3204','3205','3206','3207','3208','3209','3210','3301','3302'),
            '616F' =>  array('201','202','203','204','205','206','207','208','209','210','211','212','213','214','215','216','217','218','219','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315','316','317','318','319','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','419','501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','517','518','519','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','617','618','619','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','717'),
            '1901C-TH' =>  array('1','2','3','4','5','6','7','8'),
            '1224V' =>  array('200','201','202','203','204','205','206','207','208','209','210','211','212','213','214','215','216','217','218','219','220','221','222','223','224','225','226','300','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315','316','317','318','319','320','321','322','323','324','325','326','400','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','419','420','421','422','423','424','425','426','500','501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','517','518','519','520','521','522','523','524','525','526','600','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','617','618','619','620','621','622','623','624','625','626','700','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','717','718','719','720','721','722','723','724','725','726','800','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','817','818','819','820','821','822','823','824','825','826'),
            '30O' =>  array('1A','2A','2B', '3A','3B','4A','4B','4C','4D','4E','5A','5B','5C','5D','5E','6A','6B','6C','6D','6F', '7A','7B', '8A','8B', '9A','9B', '10A','10B', '11A','11B', '12A','12B', '13A','13B', '14A','14B', '15A','15B', '16A','16B', '17A','17B', '18A','18B', '19A','19B', '20A','20B', '21A','21B', '22A','22B', '23A','23B', '24A','24B' ),
            '901M' =>  array('401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','419','420','421','422','501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','517','518','519','520','521','522','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','617','618','619','620','621','622','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','717','718','719','720','721','722','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','817','818','819','820','821','822','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','916','917','918','919','920','921','922','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1016'),
            '70H' =>  array('302', '303', '304', '305', '306', '307', '308', '309', '310', '401', '402', '403', '404', '405', '405', '406', '407', '408', '409', '410', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1310', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2110', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2210', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2309', '2310', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2409', '2410', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2509', '2510', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2609', '2610'),
            '212C' =>  array('400', '401', '402', '403', '404', '406', '407', '408', '409', '410', '411', '412', '500', '501', '502', '503', '504', '506', '507', '508', '509', '510', '511', '512', '600', '601', '602', '603', '604', '606', '607', '608', '609', '610', '611', '612', '700', '701', '702', '703', '704', '706', '707', '708', '709', '710', '711', '712', '800', '801', '802', '803', '804', '806', '807', '808', '809', '810', '811', '812', '900', '901', '902', '903', '904', '906', '907', '908', '909', '910', '911', '912', '1000', '1001', '1002', '1003', '1004', '1006', '1007', '1008', '1009', '1010', '1011', '1100', '1101', '1102', '1103', '1104', '1106', '1107', '1108', '1109', '1110', '1111', '1200', '1201', '1202', '1203', '1204', '1206', '1207', '1208', '1209', '1210', '1211', '1300', '1301', '1302', '1303', '1304', '1306', '1307', '1308', '1309', '1310', '1311'),
            '1700E56' =>  array('301','302','304','305','306','307','308','309','310','401','402','403','404','405','406','407','408','409','410','501','502','503','503','504','505','506','507','508','509','509','510','601','602','603','','604','605','606','606','607','608','609','610','701','702','703','704','705','706','707','708','709','710','801','802','803','804','805','806','807','808','809','810','901','902','903','904','905','905','906','907','908','909','910','','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2301','2302','2303','2304','2305','2306','2306','2307','2308','2309','2310','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','3001','3002','3003','3004','3005','','3006','3007','3008','3009','3010','3101','3102','3103','3104','3105','3106','3107','3108','3109','3110','3201','3202','3203','3204','3205','3206','3207','3208','3209','3210','3301','3302','3303','3304','3305','3306','3307','3308','3309','3310','3401','3402','3403','3404','3405','3406','3407 ','3408','3409','3410','3501','3502','3503','3504','3505','3506','3507','3508','3509','3510','3601','3602','3603','3604','3604','3605','3606','3607','3608','3609','3610','3701','3702','3703','3704','3705','3706','3707','3708','3709','3710','3801','3802  ','3803','3804','3805','3806','3807','3808','3809','3810','3901','3902','3903','3904','3905','3906','3907','3908','3909','3910'),
            '57D' =>  array( '902', '1001', '1002', '1003', '1004', '1005', '1006', '1101', '1102', '1103', '1104', '1105', '1106', '1201', '1202', '1203', '1204', '1205', '1206', '1301', '1302', '1303', '1304', '1305', '1306', '1401', '1402', '1403', '1405', '1406', '1502', '1503', '1504', '1505', '1506', '1601', '1602', '1603', '1604', '1605', '1606', '1701', '1702', '1703', '1704', '1705', '1706', '1801', '1802', '1803', '1804', '1805', '1806', '1901', '1902', '1903', '1904', '1905', '1906', '2001', '2002', '2004', '2005', '2006', '2101', '2102', '2103', '2104', '2105', '2106', '2201', '2202', '2203', '2204', '2205', '2206', '2301', '2302', '2303', '2304', '2305', '2401', '2402', '2403', '2404', '2405', '2406', '2501', '2502', '2503', '2504', '2505', '2506', '2601', '2602', '2603', '2604', '2605', '2606', '2701', '2702', '2703', '2704', '2705', '2706', '2802', '2804', '2805', '2806', '2901', '2902', '2903', '2905', '2906', '3001', '3002', '3003', '3004', '3005', '3006', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3202', '3203', '3204', '3205', '3206', '3301', '3303', '3304', '3305', '3306', '3401', '3402', '3403', '3404', '3405', '3406', '3501', '3502', '3503', '3504', '3505', '3506', '3601', '3602', '3603', '3604', '3605', '3606', '3701', '3702', '3703', '3704', '3705', '3706', '3801', '3802', '3803', '3804', '3805', '3806', '3901', '3902', '3903', '3904', '3905', '3906', '4002', '4005', '4100', '4101'),
            '501C' =>  array('401', '402', '403', '404', '405', '406', '501', '502', '503', '504', '505', '601', '602', '603', '604', '605', '606', '607', '701', '702', '703', '704', '705', '706', '707', '801', '802', '803', '804', '805', '806', '807', '901', '902', '903', '904', '905', '906', '907', '1001', '1002', '1004', '1005', '1006', '1007', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1201', '1202', '1203', '1204', '1205', '1206', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2601', '2602', '2604', '2605', '2606', '2607', '2701', '2702', '2703', '2704', '2705', '2706', '2707', '2801', '2802', '2803', '2804', '2805', '2806', '2807', '2901', '2902', '2903', '2904', '2905', '2906', '2907', '3001', '3002', '3003', '3004', '3005', '3006', '3007', '3101', '3102', '3103', '3104', '3105', '3106', '3201', '3203', '3204', '3205', '3301', '3302', '3303', '3304', '3305', '3401', '3402', '3403', '3404', '3405'),
            'BIB' =>  array('2A','2B','2C','2D','2E','3A','3B','3C','3D','3E','4A','4B','4C','4D','4E','5A','5B','5C','5D','5E','601','602','607','608','701','702','703','704','705','706','707','801','802','803','804','805','806','807','901','902','903','904','905','906','907','1001','1002','1003','1004','1005','1006','1007','1101','1102','1103','1104','1105','1106','1107','1201','1202','1203','1204','1205','1206','1207','1301','1302','1303','1304','1305','1306','1307','1401','1402','1403','1404','1405','1406','1407','1501','1502','1503','1504','1505','1506','1507','1601','1602','1603','1604','1605','1606','1607','1701','1702','1703','1704','1705','1706','1707','1801','1802','1803','1804','1805','1806','1807','1901','1902','1903','1904','1905','1906','1907','2001','2002','2003','2004','2005','2006','2007','2101','2102','2103','2104','2105','2106','2107','2201','2202','2203','2204','2205','2206','2207','2301','2302','2303','2304','2305','2306','2307','2401','2402','2403','2404','2405','2406','2407','2501','2502','2503','2504','2505','2506','2507','2601','2602','2603','2604','2605','2606','2607','2701','2702','2703','2704','2705','2706','2707','2801','2802','2803','2804','2805','2806','2807','2901','2902','2903','2904','2905','2906','2907','3001','3002','3003','3004','3005','3006','3007','3101','3102','3103','3104','3105','3106','3107','3201','3202','3203','3204','3205','3206','3207','3301','3302','3303','3304','3305','3401','3402','3403','3404','3405','3501','3502','3504','3503'),
            '3550L' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '221', '222', '223', '224', '225', '226', '227', '228', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '316', '317', '318', '319', '320', '321', '322', '323', '324', '325', '326', '327', '328', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '421', '422', '423', '424', '425', '426', '427', '428', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '521', '522', '523', '524', '525', '526', '527', '528', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '621', '622', '623', '624', '625', '626', '627', '628', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720', '721', '722', '723', '724', '725', '726', '727', '728', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '813', '814', '815', '816', '817', '818', '819', '820', '821', '822', '823', '824', '825', '826', '827', '828', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '920', '921', '922', '923', '924', '925', '926', '927', '928', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019', '1020', '1021', '1022', '1023', '1024', '1025', '1026', '1027', '1028', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1113', '1114', '1115', '1116', '1117', '1118', '1119', '1120', '1121', '1122', '1123', '1124', '1125', '1126', '1127', '1128', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1211', '1212', '1213', '1214', '1215', '1216', '1217', '1218', '1219', '1220', '1221', '1222', '1223', '1224', '1225', '1226', '1227', '1228', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1310', '1311', '1312', '1313', '1314', '1315', '1316', '1317', '1318', '1319', '1320', '1321', '1322', '1323', '1324', '1325', '1326', '1327', '1328', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1411', '1412', '1413', '1414', '1415', '1416', '1417', '1418', '1419', '1420', '1421', '1422', '1423', '1424', '1425', '1426', '1427', '1428', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1511', '1512', '1513', '1514', '1515', '1516', '1517', '1518', '1519', '1520', '1521', '1522', '1523', '1524', '1525', '1526', '1527', '1528', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1611', '1612', '1613', '1614', '1615', '1616', '1617', '1618', '1619', '1620', '1621', '1622', '1623', '1624', '1625', '1626', '1627', '1628', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1711', '1712', '1713', '1714', '1715', '1716', '1717', '1718', '1719', '1720', '1721', '1722', '1723', '1724', '1725', '1726', '1727', '1728', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1812', '1813', '1814', '1815', '1816', '1817', '1818', '1819', '1820', '1821', '1822', '1823', '1824', '1825', '1826', '1827', '1828', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '1911', '1912', '1913', '1914', '1915', '1916', '1917', '1918', '1919', '1920', '1921', '1922', '1923', '1924', '1925', '1926', '1927', '1928', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025', '2026', '2027', '2028', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2110', '2111', '2112', '2113', '2114', '2115', '2116', '2117', '2118', '2119', '2120', '2121', '2122', '2123', '2124', '2125', '2126', '2127', '2128', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2210', '2211', '2212', '2213', '2214', '2215', '2216', '2217', '2218', '2219', '2220', '2221', '2222', '2223', '2224', '2225', '2226', '2227', '2228', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2309', '2310', '2311', '2312', '2313', '2314', '2315', '2316', '2317', '2318', '2319', '2320', '2321', '2322', '2323', '2324', '2325', '2326', '2327', '2328', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2409', '2410', '2411', '2412', '2413', '2414', '2415', '2416', '2417', '2418', '2419', '2420', '2421', '2422', '2423', '2424', '2425', '2426', '2427', '2428', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2509', '2510', '2511', '2512', '2513', '2514', '2515', '2516', '2517', '2518', '2519', '2520', '2521', '2522', '2523', '2524', '2525', '2526', '2527', '2528', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2609', '2610', '2611', '2612', '2613', '2614', '2615', '2616', '2617', '2618', '2619', '2620', '2621', '2622', '2623', '2624', '2625', '2626', '2627', '2628', '2701', '2702', '2703', '2704', '2705', '2706', '2707', '2708', '2709', '2710', '2711', '2712', '2713', '2714', '2715', '2716', '2717', '2718', '2719', '2720', '2721', '2722', '2723', '2724', '2725', '2726', '2727', '2728'),
            '845F' =>  array('201','202','203','204','205','206','207','208','209','210','211','212','213','214','215','216','217','218','219','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315','316','317','318','319'),
            '210D' =>  array('201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1211', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1410', '1411', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1510', '1511', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1610', '1611', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1710', '1711', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1810', '1811', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '1910', '1911', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2110', '2111', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2210', '2211'),
            '1235P' =>  array('502', '503', '504', '505', '506', '507', '508', '601', '602', '603', '604', '605', '606', '607', '608', '609', '701', '702', '703', '704', '705', '706', '707', '708', '709', '801', '802', '803', '804', '805', '806', '807', '808', '809', '901', '902', '903', '904', '905', '906', '907', '908', '909', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1301', '1302', '1303', '1304', '1305', '1306', '1307', '1308', '1309', '1401', '1402', '1403', '1404', '1405', '1406', '1407', '1408', '1409', '1501', '1502', '1503', '1504', '1505', '1506', '1507', '1508', '1509', '1601', '1602', '1603', '1604', '1605', '1606', '1607', '1608', '1609', '1701', '1702', '1703', '1704', '1705', '1706', '1707', '1708', '1709', '1801', '1802', '1803', '1804', '1805', '1806', '1807', '1808', '1809', '1901', '1902', '1903', '1904', '1905', '1906', '1907', '1908', '1909', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '2108', '2109', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209', '2301', '2302', '2303', '2304', '2305', '2306', '2307', '2308', '2309', '2401', '2402', '2403', '2404', '2405', '2406', '2407', '2408', '2409', '2501', '2502', '2503', '2504', '2505', '2506', '2507', '2508', '2509', '2601', '2602', '2603', '2604', '2605', '2606', '2607', '2608', '2609', '2701', '2702', '2703', '2704', '2705', '2706', '2707', '2708', '2709', '2801', '2802', '2803', '2804', '2805', '2806', '2807', '2808', '2809', '2901', '2902', '2903', '2904', '2905', '2906', '2907', '2908', '2909', '3001', '3002', '3003', '3004', '3005', '3006', '3007', '3008', '3009', '3101', '3102', '3103', '3104', '3105', '3106', '3107', '3108', '3109', '3201', '3202', '3203', '3204', '3205', '3206', '3207', '3208', '3209', '3301', '3302', '3303', '3304', '3305', '3306', '3307', '3308', '3309', '3401', '3402', '3403', '3404', '3405', '3406', '3407', '3408', '3409', '3501', '3502', '3503', '3504', '3505', '3506', '3507', '3508', '3509', '3601', '3602', '3603', '3604', '3605', '3606', '3607', '3608', '3609'),
            '1901C' =>  array('601','602','603','604','605','606','607','608','609','610','611','612','701','702','703','704','705','706','707','708','709','710','711','712','801','802','803','804','805','806','807','808','809','810','811','812','901','902','903','904','905','906','907','908','909','910','911','912','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1412','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1511','1512','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1611','1612','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1711','1712','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1812','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','1911','1912','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2111','2112','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2211','2212','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2311','2312','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2411','2412','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2511','2512','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2611','2612','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2711','2712','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2811','2812','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','2911','2912'),
            '270P' =>  array('101', '201', '202', '203', '301', '302', '303', '401', '402', '403', '501', '502', '503', '601', '602', '603', '701', '702', '703', '801', '803', '901', '902', '903', '1001', '1002', '1003', '1101', '1102', '1103', '1201', '1202', '1203', '1301', '1302', '1401', '1402', '1501', '1502', '16 PHE', '16 PHW'),
            '41E8' =>  array('2A','2B','2C','2D','2E','3A','3B','3C','3D','3E','4A','4B','4C','4D','4E','5A','5B','5C','5D','5E','601','602','607','608','701','702','703','704','705','706','707','801','802','803','804','805','806','807','901','902','903','904','905','906','907','1001','1002','1003','1004','1005','1006','1007','1101','1102','1103','1104','1105','1106','1107','1201','1202','1203','1204','1205','1206','1207','1301','1302','1303','1304','1305','1306','1307','1401','1402','1403','1404','1405','1406','1407','1501','1502','1503','1504','1505','1506','1507','1601','1602','1603','1604','1605','1606','1607','1701','1702','1703','1704','1705','1706','1707','1801','1802','1803','1804','1805','1806','1807','1901','1902','1903','1904','1905','1906','1907','2001','2002','2003','2004','2005','2006','2007','2101','2102','2103','2104','2105','2106','2107','2201','2202','2203','2204','2205','2206','2207','2301','2302','2303','2304','2305','2306','2307','2401','2402','2403','2404','2405','2406','2407','2501','2502','2503','2504','2505','2506','2507','2601','2602','2603','2604','2605','2606','2607','2701','2702','2703','2704','2705','2706','2707','2801','2802','2803','2804','2805','2806','2807','2901','2902','2903','2904','2905','2906','2907','3001','3002','3003','3004','3005','3006','3007','3101','3102','3103','3104','3105','3106','3107','3201','3202','3203','3204','3205','3206','3207','3301','3302','3303','3304','3305','3401','3402','3403','3404','3405','3501','3502','3504','3503'),
            '1550B' =>  array( '300', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '418', '419', '420', '421', '422', '423', '424', '425', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '516', '517', '518', '519', '520', '521', '522', '523', '524', '525', '600', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '616', '617', '618', '619', '620', '621', '622', '623', '624', '625', '700', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720', '721', '722', '723', '724', '725', '800', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '811', '812', '813', '814', '814', '815', '816', '817', '818', '819', '820', '821', '822', '823', '824', '825', '900', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '920', '921', '922', '923', '924', '925', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009', '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019', '1020', '1021', '1022', '1023', '1024', '1025', '1101', '1102', '1103', '1104', '1105', '1106', '1107', '1108', '1109', '1110', '1111', '1112', '1114', '1115', '1116', '1117', '1118', '1119', '1120', '1121', '1122', '1123', '1124', '1125', '1200', '1201', '1202', '1203', '1204', '1205', '1206', '1207', '1208', '1209', '1210', '1211'),
            '77W' =>  array('21A','21B','21C','21D','21E','21F','22A','22B','22C','22D','22E','22F','23A','23B','23C','23D','23E','23F','24A','24B','24C','24D','24E','24F','25A','25B','25C','25D','25E','25F','26A','26B','26C','26D','26E','26F','27A','27B','27C','27D','27E','27F','28A','28B','28C','28D','28F'),
            '2000M' =>  array('101', '102', '103', '104', '105', '106', '107', '201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '211', '212', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312'),
            '900C' =>  array('201','202','203','204','205','206','207','208','209','210','211','212','213','214','215','216','217','218','219','220','301','302','303','304','305','306','307','308','309','310','311','312','313','314','315','316','317','318','319','320','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','419','420','501','502','503','504','505','506','507','508','509','510','511','512','513','514','515','516','517','518','519','520','601','602','603','604','605','606','607','608','609','610','611','612','613','614','615','616','617','618','619','620','701','702','703','704','705','706','707','708','709','710','711','712','713','714','715','716','717','718','719','720','801','802','803','804','805','806','807','808','809','810','811','812','813','814','815','816','817','818','819','820','901','902','903','904','905','906','907','908','909','910','911','912','913','914','915','916','917','918','919','920','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1012','1013','1014','1015','1016','1017','1018','1019','1020','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1112','1113','1114','1115','1116','1117','1118','1119','1120','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1212','1213','1214','1215','1216','1217','1218','1219','1220','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1312','1313','1314','1315','1316','1317','1318','1319','1320','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1412','1413','1414','1415','1416','1417','1418','1419','1420','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1511','1512','1513','1514','1515','1516','1517','1518','1519','1520','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1611','1612','1613','1614','1615','1616','1617','1618','1619','1620','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1711','1712','1713','1714','1715','1716','1717','1718','1719','1720','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1812','1813','1814','1815','1816','1817','1818','1819','1820','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','1911','1912','1913','1914','1915','1916','1917','1918','1919','1920','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2013','2014','2015','2016','2017','2018','2019','2020','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2111','2112','2113','2114','2115','2116','2117','2118','2119','2120','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2211','2212','2213','2214','2215','2216','2217','2218','2219','2220','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2311','2312','2313','2314','2315','2316','2317','2318','2319','2320'),
            'BIB2' =>  array('2A','2B','2C','2D','2E','3A','3B','3C','3D','3E','4A','4B','4C','4D','4E','5A','5B','5C','5D','5E','601','602','607','608','701','702','703','704','705','706','707','801','802','803','804','805','806','807','901','902','903','904','905','906','907','1001','1002','1003','1004','1005','1006','1007','1101','1102','1103','1104','1105','1106','1107','1201','1202','1203','1204','1205','1206','1207','1301','1302','1303','1304','1305','1306','1307','1401','1402','1403','1404','1405','1406','1407','1501','1502','1503','1504','1505','1506','1507','1601','1602','1603','1604','1605','1606','1607','1701','1702','1703','1704','1705','1706','1707','1801','1802','1803','1804','1805','1806','1807','1901','1902','1903','1904','1905','1906','1907','2001','2002','2003','2004','2005','2006','2007','2101','2102','2103','2104','2105','2106','2107','2201','2202','2203','2204','2205','2206','2207','2301','2302','2303','2304','2305','2306','2307','2401','2402','2403','2404','2405','2406','2407','2501','2502','2503','2504','2505','2506','2507','2601','2602','2603','2604','2605','2606','2607','2701','2702','2703','2704','2705','2706','2707','2801','2802','2803','2804','2805','2806','2807','2901','2902','2903','2904','2905','2906','2907','3001','3002','3003','3004','3005','3006','3007','3101','3102','3103','3104','3105','3106','3107','3201','3202','3203','3204','3205','3206','3207','3301','3302','3303','3304','3305','3401','3402','3403','3404','3405','3501','3502','3504','3503'),
            '240I' =>  array('301','302','303','304','305','306','307','308','309','310','311','401','402','403','404','405','406','407','408','409','410','411','501','502','503','504','505','506','507','508','509','510','511','601','602','603','604','605','606','607','608','609','610','611','701','702','703','704','705','706','707','708','709','710','711','801','802','803','804','805','806','807','808','809','810','811','901','902','903','904','905','906','907','908','909','910','911','1001','1002','1003','1004','1005','1006','1007','1008','1009','1010','1011','1101','1102','1103','1104','1105','1106','1107','1108','1109','1110','1111','1201','1202','1203','1204','1205','1206','1207','1208','1209','1210','1211','1301','1302','1303','1304','1305','1306','1307','1308','1309','1310','1311','1401','1402','1403','1404','1405','1406','1407','1408','1409','1410','1411','1501','1502','1503','1504','1505','1506','1507','1508','1509','1510','1511','1601','1602','1603','1604','1605','1606','1607','1608','1609','1610','1611','1701','1702','1703','1704','1705','1706','1707','1708','1709','1710','1711','1801','1802','1803','1804','1805','1806','1807','1808','1809','1810','1811','1901','1902','1903','1904','1905','1906','1907','1908','1909','1910','1911','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2101','2102','2103','2104','2105','2106','2107','2108','2109','2110','2111','2201','2202','2203','2204','2205','2206','2207','2208','2209','2210','2211','2301','2302','2303','2304','2305','2306','2307','2308','2309','2310','2311','2401','2402','2403','2404','2405','2406','2407','2408','2409','2410','2411','2501','2502','2503','2504','2505','2506','2507','2508','2509','2510','2511','2601','2602','2603','2604','2605','2606','2607','2608','2609','2610','2611','2701','2702','2703','2704','2705','2706','2707','2708','2709','2710','2711','2801','2802','2803','2804','2805','2806','2807','2808','2809','2810','2811','2901','2902','2903','2904','2905','2906','2907','2908','2909','2910','2911','3001','3002','3003','3004','3005','3006','3007','3008','3009','3010','3011','3101','3102','3103','3104','3105','3106','3107','3108','3109','3110','3111'),
            '333D' =>  array( '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '311', '312', '313', '314', '315', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '515', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '611', '612', '613', '614', '615', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '711', '712', '713', '714', '715'));

         $address = Address::where('id_buildings', $buildingId)
            ->whereNull('unit')
            ->whereNull('id_customers')
            ->first();

        if($address == null) {
            return false;
        }

        // If we have unit numbers for the specified building then add/update it as a building property
        if(isset($unitNumberMap[$address->code])){
            $this->findOrCreateBuildingPropertyValue($buildingId, config('const.building_property.unit_numbers'),
                                                     json_encode(array($address->id => $unitNumberMap[$address->code])));
            return true;
        }

        return false;

    }
}
?>
