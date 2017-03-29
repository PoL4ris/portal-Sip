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
use App\Models\Note;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Port;
use App\Models\Product;
use App\Models\ProductProperty;
use App\Models\ProductPropertyValue;
use App\Models\Profile;
use App\Models\Reason;
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
                              'billingTransactionLog'           => ['App\Models\Legacy\BillingTransactionLogOld',  'LogID', 'App\Models\BillingTransactionLog']);

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

        $this->startProgressBar();
        if($this->output != null){
            $this->output->writeln('Display this on the screen');
        }

        return;

        $units = 50;

        $output = new ConsoleOutput();
        //        $output->setFormatter(new OutputFormatter(true));

        // create a new progress bar (50 units)
        $progress = new ProgressBar($output, $units);
        //        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        //        $progress->setFormat('table1:   %current% [%bar%] %percent:3s%%       %estimated:-6s%');

        $progress->setFormatDefinition('custom', ' %table%:    %current%/%max% [%bar%] %percent:3s%%       %estimated:-6s%');
        $progress->setFormat('custom');

        $i = 0;
        while ($i++ < $units) {

            //            $progress->setMessage('Importing ...');
            $progress->setMessage($i, 'table');

            // advance the progress bar 1 unit
            $progress->advance();
            // $progress->setProgress($progress);

            // you can also advance the progress bar by more than 1 unit
            // $progress->advance(3);
            usleep(500000);
        }
        $progress->finish();
        $output->writeln('');
    }

    public function migrateCustomersTable(){
        $legacyTableName = 'customers';
        $this->tableMap[$legacyTableName][3] = function($legacyCustomer){
            $this->updateCustomer($legacyCustomer, new Customer);
            $this->updateAddressByCustomer($legacyCustomer, new Address);
            $this->updatePaymentMethod($legacyCustomer, new PaymentMethod);
            $this->addContactsForCustomer($legacyCustomer);
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

        DataMigration::firstOrCreate(['table_name' => 'categories']);
        DataMigration::firstOrCreate(['table_name' => 'contacts']);

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
        Status::firstOrCreate(['id' => 1, 'name' => 'DISABLED']);
        Status::firstOrCreate(['id' => 2, 'name' => 'ACTIVE']);
        Status::firstOrCreate(['id' => 3, 'name' => 'active']);
        Status::firstOrCreate(['id' => 4, 'name' => 'disabled']);
        Status::firstOrCreate(['id' => 5, 'name' => 'new']);
        Status::firstOrCreate(['id' => 6, 'name' => 'decommissioned']);
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

        $this->startProgressBar(18, 'truncating tables');
        $tables = ['users', 'apps', 'customers', 'buildings', 'building_properties',
                   'building_property_values', 'products', 'product_properties',
                   'product_property_values', 'customer_products', 'building_products',
                   'network_nodes', 'ports', 'ticket_reasons', 'tickets', 'ticket_history',
                   'billing_transaction_logs', 'data_migrations'];

        $count = 1;
        foreach($tables as $table) {
            DB::table('users')->truncate();
            $this->advanceProgressBar($count);
            $count++;
        }
        $this->stopProgressBar();
    }

    #############################
    # Supporting functions
    #############################

    public function migrateTable($legacyTableName, $migrationDataMap, $startingId = -1, $customQueryFuction = null){

        $legacyDataModelName = $migrationDataMap[0];
        $legacyModelId = $migrationDataMap[1];
        $newDataModelName = $migrationDataMap[2];
        $customFunc = $migrationDataMap[3];

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');

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

        // Set the progress bar to 0 so it displays on the screen
        $this->advanceProgressBar(0, 0);

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
        Log::info('Migrated '.$dataMigration->records_processed.' '.$legacyTableName.' records');
        return true;
    }

    public function maxMysqlTimestamp($timestamp1, $timestamp2, $timezone1 = 'America/Chicago', $timezone2 = 'America/Chicago'){
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

    protected function startProgressBar($units = 0, $component = ''){

        if($this->console == false){ return; }

        $this->progress = new ProgressBar($this->output, $units);
        $this->progress->setFormatDefinition('custom', ' %component%:    %current%/%max% [%bar%] %percent:3s%%       %estimated:-6s%');
        $this->progress->setFormat('custom');
        $this->progress->setMessage($component, 'component');
        //        $output->setFormatter(new OutputFormatter(true));
    }

    protected function advanceProgressBar($count = 0, $progress = null){

        if($this->progress == null) { return; }

        if($count > 0) {
            $this->progress->advance($count);
            return;
        }

        if($progress != null) {
            $this->progress->setProgress($progress);
            return;
        }
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

    ########################################
    # Creation and maintenance functions
    ########################################

    public function findOrCreateCustomer(CustomerOld $legacyCustomer) {

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

            $customer->id_status = 1;
        } else {
            $customer->id_status = 2;
        }
        $customer->signedup_at = $legacyCustomer->DateSignup;
        $customer->created_at = $legacyCustomer->created_at;
        $customer->updated_at = $legacyCustomer->updated_at;
        $customer->save();
        return true;
    }

    public function findOrCreateAddressByCustomer(CustomerOld $legacyCustomer) {

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
        $address->created_at = $legacyCustomer->created_at;
        $serviceLocation = $legacyCustomer->serviceLocation;
        if($serviceLocation != null){
            $address->code = $serviceLocation->Shortname;
        }
        $address->save();
        return true;
    }

    public function addContactsForCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Email != ''){
            Contact::create(['id_customers' => $legacyCustomer->CID, 'id_types' => 5, 'value' => $legacyCustomer->Email]);
        }

        if($legacyCustomer->Tel != ''){
            Contact::create(['id_customers' => $legacyCustomer->CID, 'id_types' => 1, 'value' => $legacyCustomer->Tel]);
        }
    }

    protected function updateContactByCustomer(CustomerOld $legacyCustomer) {

        if($legacyCustomer->Email != ''){
            $emailContact = Contact::firstOrCreate(['id_customers' => $legacyCustomer->CID, 'id_types' => 5]);
            $emailContact->value = $legacyCustomer->Email;
            $emailContact->created_at = $legacyCustomer->created_at;
            $emailContact->save();
        }

        if($legacyCustomer->Tel != ''){
            $phoneContact = Contact::firstOrCreate(['id_customers' => $legacyCustomer->CID, 'id_types' => 1]);
            $phoneContact->value = $legacyCustomer->Tel;
            $phoneContact->created_at = $legacyCustomer->created_at;
            $phoneContact->save();
        }
    }

    public function findOrCreateAddressByBuilding(ServiceLocation $legacyLocation) {

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
        $address->created_at = $legacyLocation->created_at;
        $address->updated_at = $legacyLocation->updated_at;
        $address->save();
        return true;
    }

    public function findOrCreatePaymentMethod(CustomerOld $legacyCustomer) {

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

            $address = Address::where('id_customers', $legacyCustomer->CID)->first();
            if($address != null){
                $paymentMethod->id_address = $address->id;
            }

            $paymentMethod->save();
            return true;
        }
        return false;
    }

    public function findOrCreateBuilding(ServiceLocation $legacyLocation) {

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
        $building->created_at = $legacyLocation->created_at;
        $building->updated_at = $legacyLocation->updated_at;
        $building->save();
        return true;
    }

    public function findOrCreateBuildingProperty(ServiceLocationProperty $legacyLocationProperty) {

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
        $buildingProperty->created_at = $legacyLocationProperty->created_at;
        $buildingProperty->updated_at = $legacyLocationProperty->updated_at;
        $buildingProperty->save();
        return true;
    }

    public function updateBuildingPropertyValues(ServiceLocation $legacyLocation){

        $buildingId = $legacyLocation->LocID;

        $this->findOrCreateBuildingPropertyValue($buildingId, 1, $legacyLocation->Type);
        $this->findOrCreateBuildingPropertyValue($buildingId, 2, $legacyLocation->Units);
        $this->findOrCreateBuildingPropertyValue($buildingId, 3, $legacyLocation->ServiceType);
        $this->findOrCreateBuildingPropertyValue($buildingId, 4, $legacyLocation->ContractExpire);
        $this->findOrCreateBuildingPropertyValue($buildingId, 5, $legacyLocation->MgrCompany);
        $this->findOrCreateBuildingPropertyValue($buildingId, 6, $legacyLocation->Ethernet);
        $this->findOrCreateBuildingPropertyValue($buildingId, 7, $legacyLocation->Wireless);
        $this->findOrCreateBuildingPropertyValue($buildingId, 8, $legacyLocation->Speeds);
        $this->findOrCreateBuildingPropertyValue($buildingId, 9, $legacyLocation->Billing);
        $this->findOrCreateBuildingPropertyValue($buildingId, 10, $legacyLocation->EmailService);
        $this->findOrCreateBuildingPropertyValue($buildingId, 11, $legacyLocation->IP);
        $this->findOrCreateBuildingPropertyValue($buildingId, 12, $legacyLocation->DNS);
        $this->findOrCreateBuildingPropertyValue($buildingId, 13, $legacyLocation->Gateway);
        $this->findOrCreateBuildingPropertyValue($buildingId, 14, $legacyLocation->HowToConnect);
        $this->findOrCreateBuildingPropertyValue($buildingId, 15, $legacyLocation->Description);
        $this->findOrCreateBuildingPropertyValue($buildingId, 16, $legacyLocation->SupportNumber);
        $this->findOrCreateBuildingPropertyValue($buildingId, 17, $legacyLocation->fnImage);
        return true;
    }

    public function findOrCreateBuildingPropertyValue($buildingId, $propertyId, $value) {

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

    public function findOrCreateProduct(ProductOld $legacyProduct) {

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
        $product->created_at = $legacyProduct->created_at;
        $product->updated_at = $legacyProduct->updated_at;

        switch ($legacyProduct->ProdType) {

            case 'Internet':
                $product->id_types = 1;
                break;
            case 'Phone':
                $product->id_types = 2;
                break;
            case 'Phone-Option':
                $product->id_types = 3;
                break;
            case 'Router':
                $product->id_types = 4;
                break;
            case 'Ethernet Jack':
                $product->id_types = 5;
                break;
            case 'Other':
                $product->id_types = 6;
                break;
            case 'Cable Run':
                $product->id_types = 11;
                break;
            case 'Activation Fee':
                $product->id_types = 12;
                break;
            default:
                break;
        }

        $product->save();
        return true;
    }

    public function findOrCreateProductProperty(ProductPropertyOld $legacyProductProperty) {

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
        $productProperty->created_at = $legacyProductProperty->created_at;
        $productProperty->updated_at = $legacyProductProperty->updated_at;
        $productProperty->save();
        return true;
    }

    public function findOrCreateProductPropertyValue(ProductPropertyOld $legacyProductProperty) {

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
        $productPropertyValue->created_at = $legacyProductPropertyValue->created_at;
        $productPropertyValue->updated_at = $legacyProductPropertyValue->updated_at;
        $productPropertyValue->save();
        return true;
    }

    public function findOrCreateCustomerProduct(CustomerProductOld $legacyCustomerProduct) {

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
                $customerProduct->id_status = 3;
                break;
            case 'disabled':
                $customerProduct->id_status = 4;
                break;
            case 'new':
                $customerProduct->id_status = 5;
                break;
            case 'decommissioned':
                $customerProduct->id_status = 6;
                break;
            default:
                break;
        }

        $address = Address::where('id_customers', $legacyCustomerProduct->CID)->first();
        if($address != null){
            $customerProduct->id_address = $address->id;
        }

        $customerProduct->created_at = $legacyCustomerProduct->created_at;
        $customerProduct->updated_at = $legacyCustomerProduct->updated_at;
        $customerProduct->save();
        return true;
    }

    public function findOrCreateBuildingProduct(ServiceLocationProduct $legacyBuildingProduct) {

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
        $buildingProduct->created_at = $legacyBuildingProduct->created_at;
        $buildingProduct->updated_at = $legacyBuildingProduct->updated_at;
        $buildingProduct->save();
        return true;
    }

    public function findOrCreateNetworkNode(NetworkNodeOld $legacyNetworkNode) {

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
        $networkNode->role = $legacyNetworkNode->Role;
        $networkNode->properties = $legacyNetworkNode->Properties;
        $networkNode->comments = $legacyNetworkNode->Comments;

        switch ($legacyNetworkNode->Type) {

            case 'Router':
                $networkNode->id_types = 7;
                break;
            case 'Switch':
                $networkNode->id_types = 8;
                break;
            default:
                break;
        }
        $networkNode->created_at = $legacyNetworkNode->created_at;
        $networkNode->updated_at = $legacyNetworkNode->updated_at;
        $networkNode->save();
        return true;
    }

    public function findOrCreatePort(DataServicePort $legacyPort) {

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

        $port->created_at = $legacyPort->created_at;
        $port->updated_at = $legacyPort->updated_at;
        $port->save();
        return true;
    }

    public function findOrCreateReason(SupportTicketReason $legacyReason) {

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
                $reason->id_categories = 1;
                break;
            case 'PH':
                $reason->id_categories = 2;
                break;
            case 'MISC':
                $reason->id_categories = 3;
                break;
            case 'TV';
                $reason->id_categories = 4;
                break;
            default:
                break;
        }
        $reason->created_at = $legacyReason->created_at;
        $reason->updated_at = $legacyReason->updated_at;
        //        Log::info('Saving record: '.$reason->id);
        $reason->save();
        return true;
    }

    public function findOrCreateTicket(SupportTicket $legacyTicket) {

        $ticket = Ticket::find($legacyTicket->TID);

        if($ticket == null) {
            $ticket = new Ticket;
        }
        return $this->updateTicket($legacyTicket, $ticket);
    }

    protected function updateTicket(SupportTicket $legacyTicket, Ticket $ticket) {

        $ticket->id = $legacyTicket->TID;
        $ticket->id_customers = $legacyTicket->CID;
        $ticket->ticket_number = $legacyTicket->TicketNumber;
        $ticket->vendor_ticket = $legacyTicket->VendorTID;
        $ticket->id_reasons = $legacyTicket->RID;
        $ticket->comment = $legacyTicket->Comment;
        $ticket->status = $legacyTicket->Status;
        $ticket->id_users = ($legacyTicket->StaffID == '') ? 0 : $legacyTicket->StaffID;
        $ticket->id_users_assigned = ($legacyTicket->AssignedToID == '') ? 0 : $legacyTicket->AssignedToID;
        $ticket->created_at = $legacyTicket->DateCreated;
        $ticket->updated_at = $legacyTicket->LastUpdate;

        if($legacyTicket->RID == 1) {
            $ticket->id_reasons = 29;
        }

        if($legacyTicket->RID == 0) {
            $ticket->id_reasons = 1;
        }
        $ticket->save();
        return true;
    }

    public function findOrCreateTicketHistory(SupportTicketHistory $legacyTicketHistory) {

        $ticketHistory = TicketHistory::find($legacyTicketHistory->TID);

        if($ticketHistory == null) {
            $ticketHistory = new Ticket;
        }
        return $this->updateTicketHistory($legacyTicketHistory, $ticketHistory);
    }

    protected function updateTicketHistory(SupportTicketHistory $legacyTicketHistory, $ticketHistory = null) {

        if($ticketHistory == null) { $ticketHistory = new TicketHistory; }
        $ticketHistory->id = $legacyTicketHistory->THID;
        $ticketHistory->id_tickets = $legacyTicketHistory->TID;
        $ticketHistory->id_reasons = $legacyTicketHistory->RID;
        $ticketHistory->comment = $legacyTicketHistory->Comment;
        $ticketHistory->status = $legacyTicketHistory->Status;
        $ticketHistory->id_users = ($legacyTicketHistory->StaffID == '') ? 0 : $legacyTicketHistory->StaffID;
        $ticketHistory->id_users_assigned = ($legacyTicketHistory->AssignedToID == '') ? 0 : $legacyTicketHistory->AssignedToID;

        if($legacyTicketHistory->RID == 1) {
            $ticketHistory->id_reasons = 29;
        }

        if($legacyTicketHistory->RID == 0) {
            $ticketHistory->id_reasons = 1;
        }
        $ticketHistory->created_at = $legacyTicketHistory->created_at;
        $ticketHistory->updated_at = $legacyTicketHistory->updated_at;
        $ticketHistory->save();
        //        $ticketHistory = null;
        return true;
    }

    public function findOrCreateTransactionLog(BillingTransactionLogOld $legacyTransactionLog) {

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
        $transactionLog->created_at = $legacyTransactionLog->created_at;
        $transactionLog->updated_at = $legacyTransactionLog->updated_at;
        $transactionLog->save();
        return true;
    }
}
?>
