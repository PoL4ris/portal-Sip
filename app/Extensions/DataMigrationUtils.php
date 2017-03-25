<?php

namespace App\Extensions;

use DB;
use Log;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

    public function __construct() {
        DB::connection()->enableQueryLog();
        //        $queries = DB::getQueryLog();
        //        $last_query = end($queries);
        //        dd($last_query);
        //        $configPasscode = config('billing.ippay.passcode');
        //        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    protected function updateDataMigration($dataArray){
        $dataMigration = DataMigration::where('table_name', $dataArray['table_name'])->first();
        if($dataMigration == null) { return false; }
        $dataMigration->last_processed_id = $dataArray['last_processed_id'];
        $dataMigration->last_created_at = $dataArray['last_created_at'];
        $dataMigration->last_updated_at = $dataArray['last_updated_at'];
        $dataMigration->records_processed += $dataArray['record_count'];
        $dataMigration->save();
    }

    protected function getMigrationInfoArray($tableName) {
        return array('table_name' => $tableName,
                     'last_processed_id' => null,
                     'last_created_at' => null,
                     'last_updated_at' => null,
                     'record_count' => 0);
    }

    public function migrateCustomersTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('customers');
        $run = true;
        while ($run) {

            $legacyCustomers = CustomerOld::where('CID', '>', $startingId)
                ->orderBy('CID', 'asc')
                ->take($recordsPerCycle)
                ->get();

            if($legacyCustomers->count() == 0){
                break;
            }

            foreach ($legacyCustomers as $legacyCustomer) {

                $this->updateCustomer($legacyCustomer, new Customer);
                $this->updateAddressByCustomer($legacyCustomer, new Address);
                $this->updatePaymentMethod($legacyCustomer, new PaymentMethod);
                $startingId = $legacyCustomer->CID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyCustomer->CID;
                $migrationInfoArray['last_created_at'] = $legacyCustomer->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyCustomer->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' customer records');
    }

    public function migrateServiceLocationsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('serviceLocation');
        $run = true;
        while ($run) {

            $legacyLocations = ServiceLocation::where('LocID', '>', $startingId)
                ->where('LocID', '!=', 1)
                ->orderBy('LocID', 'asc')
                ->take($recordsPerCycle)
                ->get();

            if($legacyLocations->count() == 0){
                break;
            }

            foreach ($legacyLocations as $legacyLocation) {

                $result = $this->updateBuilding($legacyLocation, new Building);
                if($result == false) {
                    $startingId = $legacyLocation->LocID;
                    continue;
                }
                $this->updateAddressByBuilding($legacyLocation, new $address);
                $this->updateBuildingPropertyValues($legacyLocation);
                $startingId = $legacyLocation->LocID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyLocation->LocID;
                $migrationInfoArray['last_created_at'] = $legacyLocation->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyLocation->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' serviceLocation records');
    }

    public function migrateServiceLocationPropertiesTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('serviceLocationProperties');
        $run = true;
        while ($run) {

            $legacyLocationProperties = ServiceLocationProperties::where('PropID', '>', $startingId)
                ->orderBy('PropID', 'asc')
                ->take($recordsPerCycle)
                ->get();

            if($legacyLocationProperties->count() == 0){
                break;
            }

            foreach ($legacyLocationProperties as $legacyLocationProperty) {

                $this->updateBuildingProperty($legacyLocationProperty, new BuildingProperty);
                $startingId = $legacyLocationProperty->PropID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyLocationProperty->PropID;
                $migrationInfoArray['last_created_at'] = $legacyLocationProperty->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyLocationProperty->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' serviceLocationProperty records');
    }

    public function migrateAdditionalServiceLocationPropertyValues($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('serviceLocationPropertyValues');
        $run = true;
        while ($run) {

            $serviceLocationPropertyValues = ServiceLocationPropertyValue::where('VID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($serviceLocationPropertyValues->count() == 0){
                break;
            }

            foreach ($serviceLocationPropertyValues as $serviceLocationPropertyValue) {

                $this->findOrCreateBuildingPropertyValue($serviceLocationPropertyValue->LocID, $serviceLocationPropertyValue->PropID, $serviceLocationPropertyValue->Value);
                $startingId = $buildingPropertyValue->VID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $buildingPropertyValue->VID;
                $migrationInfoArray['last_created_at'] = $buildingPropertyValue->created_at;
                $migrationInfoArray['last_updated_at'] = $buildingPropertyValue->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' productPropertyValue records');

    }

    public function migrateProductsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('products');
        $run = true;
        while ($run) {

            $legacyProducts = ProductOld::where('ProdID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyProducts->count() == 0){
                break;
            }

            foreach ($legacyProducts as $legacyProduct) {

                $this->updateProduct($legacyProduct, new Product);
                $startingId = $legacyProduct->ProdID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyProduct->ProdID;
                $migrationInfoArray['last_created_at'] = $legacyProduct->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyProduct->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' product records');
    }

    public function migrateProductPropertiesTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('productProperties');
        $run = true;
        while ($run) {

            $legacyProductProperties = ProductPropertyOld::where('PropID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyProductProperties->count() == 0){
                break;
            }

            foreach ($legacyProductProperties as $legacyProductProperty) {

                $this->updateProductProperty($legacyProductProperty, new ProductProperty);
                $startingId = $legacyProductProperty->PropID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyProductProperty->PropID;
                $migrationInfoArray['last_created_at'] = $legacyProductProperty->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyProductProperty->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' productProperty records');
    }

    public function migrateProductPropertyValuesTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('productPropertyValues');
        $run = true;
        while ($run) {

            $legacyProductPropertyValues = ProductPropertyValueOld::where('VID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyProductPropertyValues->count() == 0){
                break;
            }

            foreach ($legacyProductPropertyValues as $legacyProductPropertyValue) {

                $this->updateProductPropertyValue($legacyProductPropertyValue, new ProductPropertyValue);
                $startingId = $legacyProductPropertyValue->VID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyProductPropertyValue->VID;
                $migrationInfoArray['last_created_at'] = $legacyProductPropertyValue->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyProductPropertyValue->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' productPropertyValue records');
    }

    public function migrateCustomerProductsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('customerProducts');
        $run = true;
        while ($run) {

            $legacyCustomerProducts = CustomerProductOld::where('CSID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyCustomerProducts->count() == 0){
                break;
            }

            foreach ($legacyCustomerProducts as $legacyCustomerProduct) {

                $this->updateCustomerProduct($legacyCustomerProduct, new CustomerProduct);
                $startingId = $legacyCustomerProduct->CSID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyCustomerProduct->CSID;
                $migrationInfoArray['last_created_at'] = $legacyCustomerProduct->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyCustomerProduct->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' customerProduct records');
    }

    public function migrateBuildingProductsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('serviceLocationProducts');
        $run = true;
        while ($run) {

            $legacyBuildingProducts = ServiceLocationProduct::where('SLPID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyBuildingProducts->count() == 0){
                break;
            }

            foreach ($legacyBuildingProducts as $legacyBuildingProduct) {

                $this->updateBuildingProduct($legacyBuildingProduct, new BuildingProduct);
                $startingId = $legacyBuildingProduct->SLPID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyBuildingProduct->SLPID;
                $migrationInfoArray['last_created_at'] = $legacyBuildingProduct->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyBuildingProduct->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' buildingProduct records');
    }

    public function migrateNetworkNodesTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('networkNodes');
        $run = true;
        while ($run) {

            $legacyNetworkNodes = NetworkNodeOld::where('NodeID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyNetworkNodes->count() == 0){
                break;
            }

            foreach ($legacyNetworkNodes as $legacyNetworkNode) {

                $this->updateNetworkNode($legacyNetworkNode, new NetworkNode);
                $startingId = $legacyNetworkNode->NodeID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyNetworkNode->NodeID;
                $migrationInfoArray['last_created_at'] = $legacyNetworkNode->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyNetworkNode->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' networkNode records');
    }

    public function migrateDataServicePortsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('dataServicePorts');
        $run = true;
        while ($run) {

            $legacyPorts = DataServicePort::where('PortID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyPorts->count() == 0){
                break;
            }

            foreach ($legacyPorts as $legacyPort) {

                $this->updatePort($legacyPort, new Port);
                $startingId = $legacyPort->PortID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyPort->PortID;
                $migrationInfoArray['last_created_at'] = $legacyPort->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyPort->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' dataServicePort records');
    }

    public function migrateSupportTicketReasonsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('supportTicketReasons');
        $run = true;
        while ($run) {

            $legacyReasons = SupportTicketReason::where('RID', '>', $startingId)
                ->orderBy('RID', 'asc')
                ->take($recordsPerCycle)
                ->get();

            if($legacyReasons->count() == 0){
                break;
            }

            foreach ($legacyReasons as $legacyReason) {

                $this->updateReason($legacyReason, new Reason);
                $startingId = $legacyReason->RID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyReason->RID;
                $migrationInfoArray['last_created_at'] = $legacyReason->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyReason->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' supportTicketReason records');
    }

    public function migrateSupportTicketsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('supportTickets');
        $run = true;
        while ($run) {

            $legacyTickets = SupportTicket::where('TID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyTickets->count() == 0){
                break;
            }

            foreach ($legacyTickets as $legacyTicket) {

                $this->updateTicket($legacyTicket, new Ticket);
                $startingId = $legacyTicket->TID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyTicket->TID;
                $migrationInfoArray['last_created_at'] = $legacyTicket->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyTicket->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' supportTicket records');
    }

    public function migrateSupportTicketHistoryTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('supportTicketHistory');
        $run = true;
        while ($run) {

            $legacyTicketHistories = SupportTicketHistory::where('THID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyTicketHistories->count() == 0){
                break;
            }

            foreach ($legacyTicketHistories as $legacyTicketHistory) {

                $this->updateTicketHistory($legacyTicketHistory, new TicketHistory);
                $startingId = $legacyTicketHistory->THID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyTicketHistory->THID;
                $migrationInfoArray['last_created_at'] = $legacyTicketHistory->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyTicketHistory->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' supportTicketHistory records');
    }

    public function migrateBillingTransactionLogsTable($startingId = -1){

        $recordsPerCycle = 200; //$this->getJobProperty('lease-request-job', 'records_per_cycle');
        $migrationInfoArray = $this->getMigrationInfoArray('billingTransactionLog');
        $run = true;
        while ($run) {

            $legacyTransactionLogs = BillingTransactionLogOld::where('LogID', '>', $startingId)
                ->take($recordsPerCycle)
                ->get();

            if($legacyTransactionLogs->count() == 0){
                break;
            }

            foreach ($legacyTransactionLogs as $legacyTransactionLog) {

                $this->updateTransactionLog($legacyTransactionLog, new BillingTransactionLog);
                $startingId = $legacyTransactionLog->LogID;
                $migrationInfoArray['record_count']++;
                $migrationInfoArray['last_processed_id'] = $legacyTransactionLog->LogID;
                $migrationInfoArray['last_created_at'] = $legacyTransactionLog->created_at;
                $migrationInfoArray['last_updated_at'] = $legacyTransactionLog->updated_at;
            }
            sleep(2);
        }
        $this->updateDataMigration($migrationInfoArray);
        Log::info('Migrated '.$migrationInfoArray['record_count'].' billingTransactionLog records');
    }

    #############################
    # Supporting functions
    #############################

    public function findOrCreateCustomer(ProductOld $legacyCustomer) {

        $customer = Customer::find($legacyCustomer->CID);

        if($customer == null) {
            $customer = new Customer;
        }
        $this->updateCustomer($legacyCustomer, $customer);
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
        $customer->save();
    }

    public function findOrCreateAddressByCustomer(CustomerOld $legacyCustomer) {

        $address = Address::where('id_customers', $legacyCustomer->CID)
            ->first();

        if($address == null) {
            $address = new Address;
        }
        $this->updateAddressByCustomer($legacyCustomer, $address);
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
        $serviceLocation = $legacyCustomer->serviceLocation;
        if($serviceLocation != null){
            $address->code = $serviceLocation->Shortname;
        }
        $address->save();
    }

    public function findOrCreateAddressByBuilding(ServiceLocation $legacyLocation) {

        $address = Address::where('id_buildings', $legacyLocation->LocID)
            ->first();

        if($address == null) {
            $address = new Address;
        }
        $this->updateAddressByBuilding($legacyLocation, $address);
    }

    protected function updateAddressByBuilding(ServiceLocation $legacyLocation, Address $address) {

        // id should already exist or be auto generated. Do not set it
        $address->address = $legacyLocation->Address;
        $address->city = $legacyLocation->City;
        $address->zip = $legacyLocation->Zip;
        $address->state = $legacyLocation->State;
        $address->country = $legacyLocation->Country;
        $address->id_buildings = $legacyLocation->LocID;
        $address->save();
    }

    public function findOrCreatePaymentMethod(CustomerOld $legacyCustomer) {

        $paymentMethod = PaymentMethod::where('id_customers', $legacyCustomer->CID)
            ->first();

        if($paymentMethod == null) {
            $paymentMethod = new PaymentMethod;
        }
        $this->updatePaymentMethod($legacyCustomer, $paymentMethod);
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

            $address = Address::where('id_customers', $legacyCustomer->CID)->first();
            if($address != null){
            $paymentMethod->id_address = $address->id;
            }

            $paymentMethod->save();
        }
    }

    public function findOrCreateBuilding(ServiceLocation $legacyLocation) {

        $building = Building::find($legacyLocation->LocID);

        if($building == null) {
            $building = new Building;
        }

        $this->updateBuilding($legacyLocation, $building);
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
        $building->save();
    }

    public function findOrCreateBuildingProperty(ServiceLocationProperty $legacyLocationProperty) {

        $buildingProperty = BuildingProperty::find($legacyLocationProperty->PropID);

        if($buildingProperty == null) {
            $buildingProperty = new BuildingProperty;
        }

        $this->updateBuildingProperty($legacyLocationProperty, $buildingProperty);
    }

    protected function updateBuildingProperty(ServiceLocationProperty $legacyLocationProperty, BuildingProperty $buildingProperty){

        $buildingProperty->id = $legacyLocationProperty->PropID;
        $buildingProperty->name = $legacyLocationProperty->->FieldTitle;
        $buildingProperty->description = $legacyLocationProperty->->Description;
        $building->save();
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
    }

    public function findOrCreateBuildingPropertyValue($buildingId, $propertyId, $value) {

        $buildingPropertyValue = BuildingPropertyValue::firstOrCreate(['id_buildings' => $buildingId, 'id_building_properties' => $propertyId]);
        $buildingPropertyValue->value = $value;
        $buildingPropertyValue->save();
    }

    public function findOrCreateProduct(ProductOld $legacyProduct) {

        $product = Product::find($legacyProduct->ProdID);

        if($product == null) {
            $product = new Product;
        }
        $this->updateProduct($legacyProduct, $product);
    }

    protected function updateProduct(ProductOld $legacyProduct, Product $product){

        $product->id = $legacyProduct->ProdID;
        $product->name = $legacyProduct->ProdName;
        $product->description = $legacyProduct->ProdDescription;
        $product->amount = $legacyProduct->Amount;
        $product->frequency = $legacyProduct->ChargeFrequency;
        $product->id_products = $legacyProduct->ParentProdID;

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
    }

    public function findOrCreateProductProperty(ProductPropertyOld $legacyProductProperty) {

        $productProperty = ProductProperty::find($legacyProductProperty->PropID);

        if($productProperty == null) {
            $productProperty = new ProductProperty;
        }
        $this->updateProductProperty($legacyProductProperty, $productProperty);
    }

    protected function updateProductProperty(ProductPropertyOld $legacyProductProperty, ProductProperty $productProperty) {

        $productProperty->id = $legacyProductProperty->PropID;
        $productProperty->name = $legacyProductProperty->FieldTitle;
        $productProperty->description = $legacyProductProperty->Description;
        $productPropertyValue->save();
    }

    public function findOrCreateProductPropertyValue(ProductPropertyOld $legacyProductProperty) {

        $productPropertyValue = ProductPropertyValue::find($legacyProductPropertyValue->VID);

        if($productPropertyValue == null) {
            $productPropertyValue = new ProductPropertyValue;
        }
        $this->updateProductPropertyValue($legacyProductProperty, $productPropertyValue);
    }

    protected function updateProductPropertyValue(ProductPropertyValueOld $legacyProductPropertyValue, ProductPropertyValue $productPropertyValue) {

        $productPropertyValue->id = $legacyProductPropertyValue->VID;
        $productPropertyValue->id_products = $legacyProductPropertyValue->ProdID;
        $productPropertyValue->id_product_properties = $legacyProductPropertyValue->PropID;
        $productPropertyValue->value = $legacyProductPropertyValue->Value;
        $productPropertyValue->save();
    }

    public function findOrCreateCustomerProduct(CustomerProductOld $legacyCustomerProduct) {

        $customerProduct = CustomerProduct::find($legacyCustomerProduct->CSID);

        if($customerProduct == null) {
            $customerProduct = new CustomerProduct;
        }
        $this->updateCustomerProduct($legacyCustomerProduct, $customerProduct);
    }

    protected function updateCustomerProduct(CustomerProductOld $legacyCustomerProduct, CustomerProduct $customerProduct) {

        if($legacyCustomerProduct->CID == null || $legacyCustomerProduct->CID == '') {
            return false;
        }

        $customerProduct->id = $legacyCustomerProduct->cp.CSID;
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
        $customerProduct->save();
    }

    public function findOrCreateBuildingProduct(ServiceLocationProduct $legacyBuildingProduct) {

        $buildingProduct = BuildingProduct::find($legacyBuildingProduct->SLPID);

        if($buildingProduct == null) {
            $buildingProduct = new BuildingProduct;
        }
        $this->updateBuildingProduct($legacyBuildingProduct, $buildingProduct);
    }

    protected function updateBuildingProduct(ServiceLocationProduct $legacyBuildingProduct, BuildingProduct $buildingProduct) {

        $buildingProduct->id = $legacyBuildingProduct->SLPID;
        $buildingProduct->id_buildings = $legacyBuildingProduct->LocID;
        $buildingProduct->id_products = $legacyBuildingProduct->ProdID;
        $buildingProduct->save();
    }

    public function findOrCreateNetworkNode(NetworkNodeOld $legacyNetworkNode) {

        $networkNode = NetworkNode::find($legacyNetworkNode->NodeID);

        if($networkNode == null) {
            $networkNode = new NetworkNode;
        }
        $this->updateNetworkNode($legacyNetworkNode, $networkNode);
    }

    protected function updateNetworkNode(DataServicePort $legacyNetworkNode, NetworkNode $networkNode) {

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
        $networkNode->save();
    }

    public function findOrCreatePort(DataServicePort $legacyPort) {

        $port = Port::find($legacyPort->PortID);

        if($port == null) {
            $port = new Port;
        }
        $this->updatePort($legacyPort, $port);
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

        $port->save();
    }

    public function findOrCreateReason(SupportTicketReason $legacyReason) {

        $reason = Reason::find($legacyReason->RID);

        if($reason == null) {
            $reason = new Reason;
        }
        $this->updateReason($legacyReason, $reason);
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
        Log::info('Saving record: '.$reason->id);
        $reason->save();
    }

    public function findOrCreateTicket(SupportTicket $legacyTicket) {

        $ticket = Ticket::find($legacyTicket->TID);

        if($ticket == null) {
            $ticket = new Ticket;
        }
        $this->updateTicket($legacyTicket, $ticket);
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
    }

    public function findOrCreateTicketHistory(SupportTicketHistory $legacyTicketHistory) {

        $ticketHistory = TicketHistory::find($legacyTicketHistory->TID);

        if($ticketHistory == null) {
            $ticketHistory = new Ticket;
        }
        $this->updateTicketHistory($legacyTicketHistory, $ticketHistory);
    }

    protected function updateTicketHistory(SupportTicketHistory $legacyTicketHistory, TicketHistory $ticketHistory) {

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
        $ticketHistory->save();
    }

    public function findOrCreateTransactionLog(BillingTransactionLogOld $legacyTransactionLog) {

        $transactionLog = BillingTransactionLog::find($legacyTransactionLog->LogID);

        if($transactionLog == null) {
            $transactionLog = new BillingTransactionLog;
        }
        $this->updateTransactionLog($legacyTransactionLog, $transactionLog);
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
        $transactionLog->save();
    }
}
?>
