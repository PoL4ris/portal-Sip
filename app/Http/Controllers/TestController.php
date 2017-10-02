<?php

namespace App\Http\Controllers;

use App\Models\BillingTransactionLog;
use Auth;
use Config;
use DB;
use Log;
use Mail;
use SendMail;
use View;

use App\Models\BuildingTicket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Extensions\SIPSignup;
use App\Extensions\SIPNetwork;
use App\Extensions\SIPCustomer;
use App\Extensions\BillingHelper;
use App\Extensions\CiscoSwitch;
use App\Extensions\MtikRouter;
use App\Extensions\DataMigrationUtils;

//use App\User;
use App\Models\Customer;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\DataMigration;
use App\Models\Address;
use App\Models\BuildingPropertyValue;
use App\Models\Building;
use App\Models\Product;
use App\Models\User;
use App\Models\Port;
use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use App\Models\RetailRevenue;
use App\Http\Controllers\TechScheduleController;
use App\Extensions\GoogleCalendar;
use DateTime;
use App\Http\Controllers\Lib\UtilsController;

//use Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Html2Text\Html2Text;


//use ActivityLogs;
use Symfony\Component\Console\Helper\ProgressBar;

class TestController extends Controller {

    public function __construct()
    {
        //        $this->middleware('auth');
        DB::connection()->enableQueryLog();
    }

    public function testCustomerTickets()
    {
        //        $customer = Customer::with('tickets')
        //                            ->find('501');    
        //        dd($customer);

        $customer = new Customer;
        $tickets = $customer->getTickets('501');
        dd($tickets->toArray());

    }

    public function testCC()
    {

        //        $customer = Customers::find('10248');
        $customer = Customer::find('10249');
        //        $customer = new Customers;
        //        $customer->Firstname = 'Peyman';
        //        $customer->Lastname = 'Pourkermani';
        //        $customer->Address = '150 N Michigan Ave';
        //        $customer->City = 'Chicago';
        //        $customer->State = 'IL';
        //        $customer->Zip = '60601';
        //        $customer->Tel = '312-600-3903';
        //        $customer->CCtype = 'MC';
        //        $customer->CCnumber = '';
        //        $customer->Expmo = '';
        //        $customer->Expyr = '';
        //        $customer->CCscode = '';
        //        $customer->save();

        //        $id = $customer->CID;
        //        $savedCustomer = Customers::find($id);
        //        dd($savedCustomer);


        $sipBilling = new SIPBilling;
        dd($sipBilling->getMode());

        //        $result = $sipBilling->authCCByCID('10247', '1.00', 'SilverIP Comm');
        //        $result = $sipBilling->authCCByCID($customer->CID, '1.00', 'SilverIP Comm');
        //        $result = $sipBilling->chargeCCByCID($customer->CID, '1.00', 'testing');
        //        $result = $sipBilling->refundCCByCID($customer->CID, '1.00', 'testing refund by CID');
        //        $result = $sipBilling->chargeCC('1.00', 'testing charge', $customer);
        //        $result = $sipBilling->refundCC('1.00', 'testing refund', $customer);
        //        $result = $sipBilling->refundCCByTransID('IP28041017IARXFUSA', 'testing refund by trans id');

//        dd($result);
    }

    public function testDBRelations()
    {

        //        $customer = Customer::with('reason3Tickets', 'type')->find(1782);
        //        $tickets = Customer::with('reason3Tickets', 'type')->find(1782);

        //        $customer = Customer::with(['tickets' => function ($query) {
        //            $query->where('id_reasons', 2);
        //        }, 'type'])->find(1782);

        //        dd($customer);

        //        $tickets = $customer->getRelationValue('tickets');
        //
        //        $collection = $tickets->each(function ($ticket, $key) {
        //            $ticket->id_reasons = 2;
        //            $ticket->save();
        //        });
        //
        //        dd($tickets);


        //        $ticket1 = Ticket::find(18685);
        //        dd($ticket1);   

        //        $customer = Customer::find($ticket1->id_customers);
        //        dd($customer);

        $ticket2 = Ticket::with('address', 'customer')->find(18685);

        //        $queries = DB::getQueryLog();
        //        $last_query = end($queries);
        //        dd($last_query);

        dd($ticket2);
    }

    public function supportTest(Request $request)
    {


        return Customer::find($request->id);

        dd(Product::with('type')->orderBy('frequency', 'asc')->get()->take(10)->toArray());
        die();
    }

    public function mail()
    {
        $customer = Customer::with('address')->find(501);
        $address = $customer->address;
        $toAddress = 'pablo@silverip.com';
        $template = 'mail.signup';
        $subject = 'dummy Test Mail';

        $data = array();
        $data['uno'] = '111';
        $data['dos'] = '222';
        $data['tres'] = '333';

        return view('mail.signup', ['customer' => $customer, 'address' => $address]);
    }

    public function cleanView()
    {
        $supController = new SupportController();


        $record = Ticket::with('customer', 'reason', 'ticketNote', 'lastTicketHistory', 'user', 'userAssigned', 'address', 'contacts')
            ->where('id_reasons', '!=', 11)
            ->where('status', '!=', 'closed')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()->toArray();

        $result = $supController->getOldTimeTicket($record);


        //    $result = Customer::with('contacts.types')
        $result = Customer::with('addresses', 'contacts', 'type', 'address.buildings', 'address.buildings.neighborhood', 'status', 'status.type', 'openTickets', 'log')
            //                             'status.type',
            //                             'openTickets')
            ->find(501)
            ->toArray();


        //      print '<pre>';
        dd($result);
        die();


        print '<pre>';

        //    $customerControllerVar = new CustomerController();
        //    $customerControllerData = $customerControllerVar->customersData();
        //
        //    print '<pre>';
        //    print_r($customerControllerData);
        //    die();

        //    print_r($last_query);

        $coso = CustomerProduct::where('id_customers', 501)->get()->toArray();

        print_r($coso);
        die();

        $coso = Customer::with('address', 'contact', 'type', 'address.buildings', 'address.buildings.neighborhood')->find(13579)->toarray();

        $queries = DB::getQueryLog();
        $last_query = end($queries);


        //    print_r($last_query);


        print '----------------------------------------------<br>';

        print_r(
            $coso
        );


        die();
    }

    public function logFunction()
    {

        $a = 4000000;
        //      $a = 63245986;
        $b = 0;

        $x = 1;
        $y = 0;
        $z = 0;
        $p = 0;

        for ($p = 0; $z < $a; $p ++)
        {


            //        print 'SERIE-->' . $p . '<br>';
            $z = $x + $y;
            $y = $x;
            $x = $z;

            if ($z % 2 == 0)
            {
                $b = $b + $z;
                print '|----- <strong>' . $b . '</strong> -----|<br>';
            }

            /**
             * Add new card
             */
            $pm = new PaymentMethod;
            $pm->id_customers = '4667';
            $pm->id_address = '33';
            $pm->types = 'Credit Card';
            $pm->card_type = 'VS';
            $pm->account_number = '4000000000000002';
            $pm->CCscode = '123';
            $pm->exp_month = '12';
            $pm->exp_year = '2019';
            $pm->billing_phone = '3126003903';
            $result = $pm->save();

            print '<pre>';
            print_r($pm);
            die();

            print '[ z ] ::----> ' . ($z) . '<br>';


            //        if($p > 2000000 && $p < 2000001)
            //          print 'Z-->' . $z . '<br>';
            //        if($p > 3000000 && $p < 3000001)
            //          print 'Z-->' . $z . '<br>';
            //        if($p > 4000000 && $p < 4000001)
            //          print 'Z-->' . $z . '<br>';

        }


        //        $queries = DB::getQueryLog();
        //        $last_query = end($queries);
        //        dd($last_query);

    }

    public function invoiceTest()
    {
        //        $customerModel = new Customer;
        //        dd($customerModel->getActiveCustomerProductsByBuildingID('28'));
        //        dd($customerModel->getActiveCustomerProductsByCustomerID('3839'));
        //        dd($customerModel->getInvoiceableCustomerProducts(null, '28'));

        //        $customer = Customer::with('payment')->find(3818);
        //        dd($customer);

        //        dd($billingHelper->getMode());


//        $building = Building::with('activeCustomerProducts.product')->find(28);
//        $activeCustomerProducts = $building->activeCustomerProducts;
//        dd($activeCustomerProducts);
//
//        $products = $activeCustomerProducts->pluck('product');
//        dd($building->activeCustomers->take(5));
//        dd($building->customerProducts);
//        dd($products->pluck('frequency'));

//        $customerProduct = CustomerProduct::find(4991);
//        dd($customerProduct->address);
//
//        $customer = Customer::find(4667);
//        dd($customer->customerProducts);

//        $charge = Charge::find(97);
//        dd($charge);


        $invoice = Invoice::find(97);
        $invoiceDetails = $invoice->details();
        $detailsCollection = collect($invoiceDetails);
        $customerProductIds = $detailsCollection->pluck('customer_product_id')->toArray();
        dd($customerProductIds);


        $charges = $invoice->charges;
        $details = $charges->pluck('details');
        foreach ($details as $chargeDetails)
        {
            dd(json_decode($chargeDetails, true));
        }
        dd($details);


        $billingHelper = new BillingHelper();

//        $billingHelper->generateResidentialChargeRecords();
        $billingHelper->invoicePendingCharges();

//        dd($billingHelper->getCustomersWithChargableProducts(28));
//        dd($billingHelper->getChargeableCustomerProductsByBuildingId(28));
//        dd($billingHelper->getChargeableCustomerProducts2(null,28));
//        dd($billingHelper->invoicePendingCharges());

//        dd($billingHelper->generateResidentialInvoiceRecords());
        //        $billingHelper->processAutopayInvoices();
    }

    public function testActivityLog()
    {
        ActivityLog::test();
    }

    public function genericTest()
    {

        //        $childProduct = Product::find(104);
        //        dd($childProduct->parentProduct);


        //        $building = Building::with('products.test')->find(6);
        $building = Building::find(71);
        dd($building->activeParentProducts()->toArray());
    }

    public function testDataMigration()
    {

        $dbMigrationUtil = new DataMigrationUtils();

        //        $dbMigrationUtil->seedAppsTable();
        //        dd('done');
        dd(DataMigration::count());

        dd(config('const.status.disabled'));
        //        $dbMigrationUtil->updateFromCustomersTable();
        //        dd($dbMigrationUtil->maxMysqlTimestamp('2017-03-26 12:32:12', '2017-03-27 12:32:12'));
        //        $dbMigrationUtil->migrateCustomersTable();
        //        $dbMigrationUtil->migrateSupportTicketHistoryTable();
        //        $dbMigrationUtil->migrateSupportTicketReasons();

        dd('done');
    }

    protected function updateMtikHotspotTarget()
    {

    }

    protected function getAnnualChargeQuery()
    {
        return Charge::where('details', 'like', '%"product_frequency":"annual"%')
            ->where('status', config('const.charge_status.invoiced'));
    }

    public function generalTest(Request $request)
    {

//        dd('done');

        /** Find duplicate annual invoices and delete them **/
        $billingHelper = new BillingHelper();


        // Get a distinct copy of the annual charges that have duplicate records
        $charges = $this->getAnnualChargeQuery()
//            ->where('id_customers', 5192)
            ->groupBy('id_customers')
            ->havingRaw('count(id) > 1')
            ->orderBy('start_date', 'asc')
//            ->take(5)
            ->get();

        dd($charges->pluck('id_customers')->sort());

        $customerCount = 0;

        // Iterate through them and weed out the duplicate ones
        foreach ($charges as $charge)
        {

            // Get the customer for this charge and verify that they are active
            $customer = Customer::find($charge->id_customers);
            if ($customer->id_status != config('const.status.active'))
            {
                Log::info('Skipping inactive customer id=' . $customer->id);
                continue;
            }

            // Get all the annual charges for the customer
            $annualCustomerCharges = $this->getAnnualChargeQuery()
                ->where('id_customers', $customer->id)
                ->orderBy('start_date', 'asc')
                ->get();

            // Pop the first charge from the list. We will keep this one to set the
            // customer product flags and dates
            $firstCharge = $annualCustomerCharges->shift();

            $customerProduct = $charge->customerProduct;
            if ($customerProduct->charge_status == 0)
            {
                $customerProduct->amount_owed = $firstCharge->amount;
            } else {
                $customerProduct->amount_owed = 0;
            }
            $monthString = date('F Y', strtotime('+1 month', strtotime($customerProduct->signed_up)));
            if($customerProduct->expires != null){
                $monthString = date('F Y', strtotime('-1 month', strtotime($customerProduct->expires)));
            }

            $billingHelper->markCustomerProductAsCharged($customerProduct, $monthString);

            foreach ($annualCustomerCharges as $chargeToRemove)
            {
                $invoice = $chargeToRemove->invoice;
                $chargeToRemove->delete();
                $billingHelper->updateInvoiceAmount($invoice);
            }
            $customerCount++;

        }

        dd('Cleaned up '.$customerCount.' customer accounts.');



        /** Show number of pending failed invoices that need to be notified **/
//        $billingHelper = new BillingHelper();
//        dd($billingHelper->getFailedPendingInvoiceToNotifyQuery()->count());

        /** Find pending invoices then check for ones that belong to disabled customers **/

//        $invoiceStatusArrayMap = array_flip(config('const.invoice_status'));

//        $invoices = Invoice::with('customer')
//            ->where('processing_type', config('const.type.auto_pay'))
//            ->where('status', config('const.invoice_status.pending'))
////            ->where('status', '!=', config('const.invoice_status.cancelled'))
////            ->where('failed_charges_count', '>', 0)
////            ->where('due_date', '2017-08-01 00:00:00')
////            ->where('amount', '<', 100)
//            ->get();

//        $invoices->transform(function ($invoice, $key) use ($invoiceStatusArrayMap) {
//            $invoice->status = $invoiceStatusArrayMap[$invoice->status];
//
//            return $invoice;
//        });

//        dd($invoices->count());
//        dd('$'.$invoices->pluck('amount', 'id_customers')->sort()->sum());

        /** Get the customer IDs that are disabled for the above invoices **/
//        $customers = $invoices->pluck('customer');
//        dd($customers->whereLoose('id_status', config('const.status.disabled'))->pluck('id_status', 'id'));


        /** Check and disable one-time products that have been charged/invoiced **/
//        $billingHelper = new BillingHelper();

//        $oneTimeCharges = Charge::with('customerProduct')
//            ->where('details', 'like', '%onetime%')
//            ->where('due_date', '2017-10-01 00:00:00')
//            ->get();

//        $customerProducts = $oneTimeCharges->pluck('customerProduct')->whereLoose('id_users', 0);

//        foreach ($customerProducts as $customerProduct)
//        {
//            $customerProduct->charge_status = config('const.customer_product_charge_status.paid');
//            $customerProduct->amount_owed = '0.00';
//            $customerProduct->save();
//
//            $activeCharge = $customerProduct->activeCharge;
//            $invoice = $activeCharge->invoice;
//            $activeCharge->delete();
//            $billingHelper->updateInvoiceAmount($invoice);
////            dd('done');
//        }

//        dd($customerProducts->pluck('charge_status', 'id'));

//        $info = array();
//
//        foreach ($oneTimeCharges as $oneTimeCharge)
//        {
//            $customer = $oneTimeCharge->customer;
//            $customerProduct = $oneTimeCharge->customerProduct;
//            $info[$customer->first_name . ' ' . $customer->last_name. ' ('.$customer->id.')'] = $customerProduct->product->description . ' (' . $customerProduct->id . ')';
//        }
//
//        dd($info);
//
//        $customerProducts = $oneTimeCharges->pluck('customerProduct');
//        $products = $customerProducts->pluck('id_products', 'signed_up');
//        dd($products);

        /** Check internet plans for 65M customers **/

//        $building = Building::where('alias', '65M')->first();
//
//        $customerProducts = $building->activeCustomerProducts;
//        $productsCollection = collect($customerProducts->pluck('product'));
//        $internetProducts = $productsCollection->filter(function ($product, $key) {
//            return $product->id_types == config('const.type.internet');
//        });
//
//        dd($internetProducts->pluck('name'));


        /** Change base plans for 65M customers **/

//        $building = Building::where('alias', '65M')->first();
//
//        $customerProducts = $building->activeCustomerProducts;
//
//        foreach ($customerProducts as $customerProduct)
//        {
//            $customer = $customerProduct->customer;
//            if ($customer == null)
//            {
//                Log::info('Customer product id=' . $customerProduct->id . ' does not have a customer. skipping.');
//                continue;
//            }
//
//            if ($customerProduct->customer->id_status == config('const.status.disabled'))
//            {
//                $customerProduct->id_status = config('const.status.disabled');
//                $customerProduct->save();
//                Log::info('Customer id='.$customer->id.' is disabled ... deactivating customer product id=' . $customerProduct->id);
//                continue;
//            }
//
//            $product = $customerProduct->product;
//            if ($product == null)
//            {
//                Log::info('Customer product id=' . $customerProduct->id . ' does not have a product. skipping.');
//                continue;
//            }
//
//            if ($customerProduct->product->id_types == config('const.type.internet'))
//            {
//                $customerProduct->id_status = config('const.status.disabled');
//                $customerProduct->save();
//                $newBasePlan = new CustomerProduct();
//                $newBasePlan->id_products = 67;
//                $newBasePlan->id_customers = $customer->id;
//                $newBasePlan->id_status = config('const.status.active');
//                $newBasePlan->id_address = $customerProduct->id_address;
//                $newBasePlan->signed_up = '2017-09-30 21:00:00';
//                $newBasePlan->id_users = 0;
//                $newBasePlan->charge_status = 0;
//                $newBasePlan->save();
//                Log::info('Added new base plan for customer id='.$customer->id);
//                continue;
//            }
//        }
//
//        dd('done');


        /** 65M switch port moves **/
//        $ports = Port::where('id_network_nodes', 905)->get();
//
//        foreach ($ports as $port)
//        {
////            $port->port_number = '1/' . ($port->port_number - 4);
//            $port->port_number = '1/' . $port->port_number;
//            $port->id_network_nodes = 1039;
////            $port->save();
//        }
//        dd($ports->sortBy('port_number')->pluck('port_number', 'id'));


        /** Cisco switch tests **/

//        $ciscoSwitch = new CiscoSwitch(['readCommunity'  => 'oomoomee',
//                                        'writeCommunity' => 'BigSeem']);
//
//        $ipArray = ['10.11.51.40', '10.11.51.140', '10.11.51.43', '10.11.51.143', '10.11.51.46', '10.11.51.47', '10.11.51.146'];
//
//        $switchInfo = array();
////        $switchInfo[] = $ciscoSwitch->getBridgePortIndex('10.11.51.146', '1/9', false);
//        $switchInfo[] = $ciscoSwitch->getSnmpPortVlanAssignment($ipArray[6], '1/9', false);
//        $switchInfo[] = $ciscoSwitch->getPortIndex($ipArray[6], '1/9', false);
//
////        $switchInfo[] = $ciscoSwitch->getSnmpModelNumber($ipArray[6]);
////        $switchInfo[] = $ciscoSwitch->getSnmpPortOperStatus($ipArray[0], '1/9');
//
//        dd($switchInfo);


//        // entPhysicalDescr
////        dd(snmp2_real_walk($ipArray[0], 'oomoomee', '1.3.6.1.2.1.47.1.1.1.1.13'));
//
//        // sysLocation
////        dd(snmp2_real_walk('10.11.122.43', 'oomoomee', '1.3.6.1.2.1.1.6')); // . '.0'
//
//        // dot1dBaseBridgeAddress
////        dd(snmp2_real_walk('10.11.122.43', 'oomoomee', '1.3.6.1.2.1.17.1.1')); // . '.0'
//
////        $switchPortInfoArray = array();
////        $portTypeRegEx = '/.*ethernet.*/i';
////        $skipLabelPattern = ['/.*[uU]plink.*/i', '/.*[dD]ownlink.*/i', '/.*CORE.*/i', '/.*CCR.*/i', '/.*SWITCH.*/i', '/.*\-.*/i'];
////        $skipLabelPattern =[];
//
//        $response = [];
//        foreach ($ipArray as $ip)
//        {
//            $hostname = preg_replace('/\.silverip\..*/', '', $ciscoSwitch->getSnmpSysName($ip));
//            $model = $ciscoSwitch->getSnmpModelNumber($ip);
//            $mac = $ciscoSwitch->getSnmpMacAddress($ip);
////            dd($mac);
//            $response[] = ['name' => $hostname['response'], 'model' => $model['response'], 'mac' => $mac['response']];
//        }
//
//        dd($response);

//        $portDescArr = $ciscoSwitch->getSnmpAllPortDesc($ip, $portTypeRegEx);
////        if(isset($portDescArr['error'])){
////            return $switchPortInfoArray;
////        }
//
//        $portLabelArr = $ciscoSwitch->getSnmpAllPortLabel($ip, $portTypeRegEx, $skipLabelPattern);
////        if(isset($portLabelArr['error'])){
////            return $switchPortInfoArray;
////        }
//
//        dd([$portDescArr, $portLabelArr]);


        /** Cleanup successful manual invoices that have not been marked paid **/

//        $billingHelper = new BillingHelper();
//
//        $invoices = Invoice::where('processing_type', 14)
//            ->where('status', 2)
//            ->where('failed_charges_count', '>', 0)
//            ->get();
//
//        foreach ($invoices as $invoice)
//        {
//
//            $invoiceLogs = $invoice->logs;
//            if ($invoiceLogs == null)
//            {
//                Log::info('Could not find any logs for invoice id=' . $invoice->id);
//                continue;
//            }
//
//            $invoiceLog = $invoiceLogs->first();
//            $transactionId = $invoiceLog->id_transactions;
//            $transaction = BillingTransactionLog::where('transaction_id', $transactionId)->first();
//
//            if ($transaction->response_text == 'APPROVED' || $transaction->response_text == 'RETURN ACCEPTED')
//            {
//                Log::info('Invoice id=' . $invoice->id . ' should be marked as paid but it\'s not.');
//                $billingHelper->updateInvoiceChargeStatus($invoice, config('const.charge_status.paid'));
//                $billingHelper->markInvoiceAsPaid($invoice);
//                $invoiceLog->status = 'processed';
//                $invoiceLog->save();
//            } else
//            {
//                Log::info('Skipping invoice id=' . $invoice->id . '. The response from billing was: ' . $transaction->response_text . ' ' . $transaction->response_error);
//            }
//        }
//
//        $charges = Charge::where('status', 2)
//            ->where('description', 'like', 'Manual%')
//            ->get();
//
//        foreach ($charges as $charge)
//        {
//            $invoice = $charge->invoice;
//            if ($invoice->status == config('const.invoice_status.paid'))
//            {
//                $invoiceLogs = $invoice->logs;
//                if ($invoiceLogs == null)
//                {
//                    Log::info('Could not find any logs for invoice id=' . $invoice->id);
//                    continue;
//                }
//
//                $invoiceLog = $invoiceLogs->first();
//                $transactionId = $invoiceLog->id_transactions;
//                $transaction = BillingTransactionLog::where('transaction_id', $transactionId)->first();
//
//                if ($transaction->response_text == 'APPROVED' || $transaction->response_text == 'RETURN ACCEPTED')
//                {
//                    Log::info('Charge id=' . $charge->id . ' should be marked as paid but it\'s not.');
//                    $billingHelper->updateInvoiceChargeStatus($invoice, config('const.charge_status.paid'));
//                    $invoiceLog->status = 'processed';
//                    $invoiceLog->save();
//                } else
//                {
//                    Log::info('Skipping charge id=' . $charge->id . '. The response from billing was: ' . $transaction->response_text . ' ' . $transaction->response_error);
//                }
//            }
//
//        }
//        dd('done');

        /** Test declined invoice notifications **/
//        $billingHelper = new BillingHelper();
//
////        $billingHelper->generateChargeRecordsForCustomerByMonth(4667, 'September');
////        $billingHelper->invoicePendingAutoPayChargesForCustomerByMonth(4667, 'September');
////        $billingHelper->generateChargeRecordsForCustomerByMonth(4667, 'October');
////        $billingHelper->invoicePendingAutoPayChargesForCustomerByMonth(4667, 'October');
////        dd('done');
//
//        $customer = Customer::find(4667);
//
//        $invoices = Invoice::where('id_customers', 4667)
//            ->where('status', config('const.invoice_status.pending'))
//            ->where('processing_type', config('const.type.auto_pay'))
//            ->orderBy('due_date', 'asc')->get();
//
//        $billingHelper->sendDeclinedChargeReminderByInvoiceCollection($customer, $invoices);
//
//        dd('done');
//
//        if ($invoices->count() > 0)
//        {
//            $firstInvoice = $invoices->first();
//            $firstInvoiceDetails = $firstInvoice->details();
//            dd($firstInvoiceDetails);
////            $productFrequency = $firstInvoiceDetails['']
//        }
//
//        dd('done');
//
//        $details = [];
//        $productFrequency = '';
//        foreach ($invoices as $invoice)
//        {
//            $details[] = $invoice->details();
////            $productFrequency =
//        }
//        dd($details);


        /** Mark paid invoice charges as paid **/
//        $billingHelper = new BillingHelper();
//
//        $perPage = 15;
//        $totalInvoicesProcessed = 0;
//
//        $paginatedInvoices = $billingHelper->paginatePendingInvoices();
//        $lastProcessedInvoiceId = 0;
//
//        while ($paginatedInvoices->count() > 0)
//        {
//            $invoices = $paginatedInvoices;
//
//            foreach ($invoices as $invoice)
//            {
//                $totalInvoicesProcessed ++;
//                Log::info('TestController::generalTest(): Updating charge details for invoice id=' . $invoice->id . ' amount=$' . $invoice->amount);
//
//                $charges = $invoice->charges;
//                if($charges == null){ continue; }
//                foreach($charges as $charge){
//                    $billingHelper->updateChargeFromCustomerProduct($charge);
//                }
//
////                $billingHelper->updateInvoiceChargeStatus($invoice, config('const.charge_status.paid'));
//                $lastProcessedInvoiceId = $invoice->id;
//            }
//
//            $paginatedInvoices = $billingHelper->paginatePendingInvoices($perPage, $lastProcessedInvoiceId);
//        }
//
//        dd('Processed ' . $totalInvoicesProcessed . ' invoices.');


        /** Update product descriptions **/
//
//        while (true)
//        {
//
//            $products = Product::where('description', '')
//                ->where(function ($query) {
//                    $query->where('frequency', 'annual')
//                        ->orWhere('frequency', 'monthly');
//                })
//                ->take(10)
//                ->get();
//
//            if ($products->count() == 0)
//            {
//                break;
//            }
//
//            foreach ($products as $product)
//            {
//                $name = $product->name;
//                $description = trim(preg_replace('/(.*)-.*/', '$1', $name));
//                $product->description = $description.' Internet Plan';
//                $product->save();
//            }
//        }
//
//        dd('done');



        /** Update customer products and charges that don't have addresses **/

//        $customerProducts = CustomerProduct::where('id_address', 0)->get();
//        $charges = Charge::where('id_address', 0)->get();
//
//        foreach ($customerProducts as $customerProduct)
//        {
//            $customer = $customerProduct->customer;
//            if($customer == null){
//                Log::info('Customer product id='.$customerProduct->id.' has no customer associated with it.');
//                $customerProduct->delete();
//                continue;
//            }
//            $address = $customer->address;
//            if($address == null){
//                Log::info('Customer id='.$customer->id.' has no address associated with it.');
//                continue;
//            }
//            $customerProduct->id_address = $address->id;
//            $customerProduct->save();
//        }
//
//        foreach ($charges as $charge)
//        {
//            $customer = $charge->customer;
//            if($customer == null){
//                Log::info('Charge id='.$charge->id.' has no customer associated with it.');
//                continue;
//            }
//            $address = $customer->address;
//            if($address == null){
//                Log::info('Customer id='.$customer->id.' has no address associated with it.');
//                continue;
//            }
//
//            $charge->id_address = $address->id;
//            $charge->save();
//        }
//
//        $customerProducts = CustomerProduct::where('id_address', 0)->get();
//        $charges = Charge::where('id_address', 0)->get();
//
//        dd(['customer products' => $customerProducts->count(), 'charges' => $charges->count()]);


        /** Test the charges and invoices page queries **/
//        $result = array();
////        $result['year'] = Date('Y');
////        $result['month'] = Date('m');
//        $result['year'] = '2017';
//        $result['month'] = '10';
//        $data = $request->all();
//
////        if (count($data) > 1)
//        $timeData = '"' . $result['year'] . '-' . $result['month'] . '-' . '0"';
////        else
////            $timeData = 'CURRENT_DATE()';
//
//        $loadResults = Charge::with('customer',
//            'address',
//            'invoice',
//            'user',
//            'productDetail.product')
//            ->whereRaw('YEAR(start_date)  = YEAR(' . $timeData . ')')
//            ->whereRaw('MONTH(start_date) = MONTH(' . $timeData . ')');
//
////        $loadResults->where('status', 3);
//        $loadResults->where('amount', 'like', '%%');
//
//        $code = '';
//        $loadResults->whereHas('address', function ($query) use ($code) {
//            $query->where('code', 'like', '%%');
//        });
//
//
//        $unit = '';
//        $loadResults->whereHas('address', function ($query) use ($unit) {
//            $query->where('unit', 'like', '%' . $unit . '%');
//        });
//
////        $loadResults->count();
////        $queries = DB::getQueryLog();
////        $last_query = end($queries);
//////        dd($queries);
////        dd($last_query);
//
//        dd($loadResults->count());


        /** Send unregistered ports to signup **/
        $building = Building::where('alias', '65M')->first();
//        $address = $building->address;

        $accessSwitches = $building->accessSwitches;
        foreach ($accessSwitches as $switch)
        {
            $ports = $switch->ports;

        }
        dd($accessSwitches);


        /** Convert HTML to text **/
//        $htmlString = '<html>
//                        <title>Ignored Title</title>
//                        <body>
//                          <h1>Hello, World!</h1>
//
//                          <p>This is some e-mail content.
//                          Even though it has whitespace and newlines, the e-mail converter
//                          will handle it correctly.
//
//                          <p>Even mismatched tags.</p>
//
//                          <div>A div</div>
//                          <div>Another div</div>
//                          <div>A div<div>within a div</div></div>
//
//                          <a href="http://foo.com">A link</a>
//
//                        </body>
//                        </html>';
//
//
//        $text = Html2Text::convert($htmlString);
//        dd($text);


        /** Show email addresses for all active customers at the specified building **/
//        $building = Building::where('nickname', '65M')->first();
//        $activeCustomers = $building->activeCustomers;
//        dd($activeCustomers->pluck('emailAddress')->pluck('value'));

        /**  Find invoices that need to be cancelled for the specified customer **/
//        $firstDayOfMonthTime = strtotime("first day of this month 00:00:00");
//        $timestampMysql = date('Y-m-d H:i:s', $firstDayOfMonthTime);
//        $customer = Customer::find(3533);
//        $pendingInvoices = $customer->pendingAutoPayInvoicesOnOrAfterTimestamp($timestampMysql);
//        dd($pendingInvoices);

        /** Find pending invoices for the specified month (due on the first of the specified month) **/
//        $invoiceStatusArrayMap = array_flip(config('const.invoice_status'));
//
//        $invoices = Invoice::with('customer')
//            ->where('processing_type', config('const.type.auto_pay'))
//            ->where('status', config('const.invoice_status.pending'))
////            ->where('status', '!=', config('const.invoice_status.cancelled'))
//            ->where('failed_charges_count', '>', 0)
////            ->where('due_date', '2017-08-01 00:00:00')
////            ->where('amount', '<', 100)
//            ->get();
//
//        $invoices->transform(function ($invoice, $key) use ($invoiceStatusArrayMap) {
//            $invoice->status = $invoiceStatusArrayMap[$invoice->status];
//
//            return $invoice;
//        });
//
//        dd($invoices->count());
//        dd('$'.$invoices->pluck('amount', 'id_customers')->sort()->sum());

        /** Get the customer IDs that are disabled for the above invoices **/
//        $customers = $invoices->pluck('customer');
//        dd($customers->whereLoose('id_status', 2)->pluck('id_status', 'id'));


        /** Test pending invoice pagination by Id **/
        $billingHelper = new BillingHelper();
//        $pendingInvoices = $billingHelper->paginatePendingInvoices();
//
//        $lastInvoice = $pendingInvoices->last();
//        $pendingInvoices = $billingHelper->paginatePendingInvoices(15, $lastInvoice->id);
//        dd($pendingInvoices);

        $billingHelper->processPendingAutopayInvoicesThatHaveUpdatedPaymentMethods();
        dd('done');


//
//
////        $expiresBeforeMysqlDate = date("Y-m-d H:i:s", strtotime('first day of this month 00:00:00'));
//        $expiresBeforeMysqlDate = date("Y-m-d H:i:s", strtotime('first day of August 00:00:00'));
//
////        $customerProducts = $billingHelper->getChargeableCustomerProductsQuery('first day of this month 00:00:00')
////            ->where('buildings.id', '=', 61)  // 730C
////            ->get(['customer_products.*']);
////
////        dd($customerProducts);
//
////        $customerProducts = $billingHelper->getChargeableCustomerProductsQuery('first day of this month 00:00:00', true)
//        $customerProducts = $billingHelper->getChargeableCustomerProductsQuery('first day of August 00:00:00', true)
//            ->where('customer_products.signed_up', '<', $expiresBeforeMysqlDate)
//            ->whereNull('customer_products.last_charged')
//            ->where('buildings.id', '=', 61)
//            ->get(['customer_products.*']);
//
//        dd($customerProducts);
//
//        $customerProduct = $customerProducts->first();
//
//        dd(number_format($customerProduct->product->amount, 2, '.', ''));


//        $billingHelper->generateResidentialChargeRecordsForLastMonth();
//        dd($billingHelper->getChargeableCustomerProductsByCustomerId(14703));

//        dd(storage_path('app'));

//        $directory = 'mikrotik_firmware/all_packages-tile-6.40.1';
//        $files = File::allFiles(storage_path('app/'.$directory));
//
//        foreach($files as $file){
//            echo $file->getPathname().'/'.$file->getFilename()  .'<br>';
//        }
//
////        $files = Storage::disk('local')->allFiles($directory);
//
////        dd(Storage::disk('local')->files('mikrotik_firmware/all_packages-tile-6.40.1'));
//
//        dd($files);
//
//        $mikrotik = NetworkNode::where('ip_address', '10.10.13.1')->first();
////        dd($mikrotik);

        $ipAddress = '192.168.100.249';
        $serviceRouter = new MtikRouter(['ip_address' => $ipAddress,
                                         'username'   => 'test',
                                         'password'   => 'testtest']);
        $softwareversion = $serviceRouter->getSoftwareVersion($ipAddress);
        $architecture = $serviceRouter->getArchitecture($ipAddress);

        dd([$softwareversion, $architecture]);


//        $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
//                                         'username'   => config('netmgmt.mikrotik.username'),
//                                         'password'   => config('netmgmt.mikrotik.password')]);
//        $softwareversion = $serviceRouter->getSoftwareVersion($mikrotik->ip_address);
//        $architecture = $serviceRouter->getArchitecture($mikrotik->ip_address);
//
//        dd([$softwareversion, $architecture]);

//            dd('done');
//        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//        dd($mikrotiks);

//        $serverIp = '108.160.193.70';
//        foreach ($mikrotiks as $mikrotik)
//        {
//            $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
//                                             'username' => config('netmgmt.mikrotik.username'),
//                                             'password' => config('netmgmt.mikrotik.password')]);
//            $serviceRouter->updateHotspotServerTarget($mikrotik->ip_address, $serverIp);
//            echo 'Updated ' . $mikrotik->host_name . '<br>';
////            dd('done');
//        }
//        dd('done');


//        $customer = Customer::with('services')->find(9992)->toArray();
//
//        dd(collect($customer['services']));
//
//        dd(date('c', strtotime('2010-05-29 01:17:35')));
//        dd(date('Y-m-d\TH:i:s.v A', strtotime('2010-05-29 01:17:35')));
//
//
//        $customerProduct =  CustomerProduct::find(9472);
//        dd([$customerProduct, $customerProduct->product, $customerProduct->activeCharge]);
//
//        $charge = Charge::find(29);
//        dd($charge->invoice);
//
//
//        $nowMysql = date("Y-m-d H:i:s");
//        $invoices = Invoice::where('status', config('const.invoice_status.pending'))
//            ->where('processing_type', config('const.type.auto_pay'))
//            ->where(function ($query) use ($nowMysql)
//            {
//                $query->where('due_date', 'is', 'NULL')
//                    ->orWhere('due_date', '<=', $nowMysql)
//                    ->orWhere('due_date', '');
//            })->get();
//
//        dd($invoices);


//
//        $sipCustomer = new SIPCustomer();
//
//        dd($sipCustomer->addNewCustomer('', '', ''));
//
//        $input = $request->all();
//
////        $customers = Customer::where('id_status', config('const.status.active'))->simplePaginate(5);
//        $customers = Customer::where('id_status', config('const.status.active'))->paginate(5);
//
//        dd([$customers, $input]);
//        $customerNames = $customers->pluck( 'last_name', 'first_name');
//        dd($customerNames);
//
//
//

//        $customer = Customer::find(4648);
//
//        $customerPorts = $customer->customerPort;
//
//        foreach ($customerPorts as $customerPort)
//        {
//            $customerAddress = $customerPort->customer->address;
//            $portAddress = $customerPort->port->address;
//            if ($portAddress->code != $customerAddress->code)
//            {
//                echo 'CustomerPort: ' . $customerPort->id . ' does not match<br>';
//            }
//        }

//        dd('done');

//        dd([$customerAddress, $portAddress]);
//        dd($customer->getNetworkInfo());

//        $customerPort = $customer->customerPort->first();


//        dd($customerPort->portWithNetworkNode);
//        dd($customerPort->networkNode);
//
//        dd($customer->getNetworkInfo());

//        $invoices = Invoice::where('processing_type', config('const.type.auto_pay'))
//            ->where('status', config('const.invoice_status.pending'))
//            ->take(1)
//            ->first();
//
//        dd($invoices->customer->defaultPaymentMethod);

        $billingHelper = new BillingHelper();

//        $billingHelper->processPendingAutopayInvoicesThatHaveUpdatedPaymentMethods();

        $invoices = $billingHelper->paginatePendingInvoices();

        $queries = DB::getQueryLog();
        $last_query = end($queries);
        dd($queries);


        dd($invoices->currentPage()); //->pluck('id'));


//    $results->count()
//    $results->currentPage()
//    $results->firstItem()
//    $results->hasMorePages()
//    $results->lastItem()
//    $results->lastPage() (Not available when using simplePaginate)
//    $results->nextPageUrl()
//    $results->perPage()
//    $results->previousPageUrl()
//    $results->total() (Not available when using simplePaginate)
//    $results->url($page)

        dd($billingHelper->getChargeableCustomerProductsByCustomerId(4667));
//
//        $invoice = Invoice::find(2955);
////        dd($invoice);
//        $billingHelper->processInvoice($invoice);

        dd('done');

//        $billingHelper->generateResidentialChargeRecords();
        $billingHelper->generateChargeRecordsForCustomer(4667);

        dd('done');

//        $billingHelper->invoicePendingCharges();

//        $invoices = Invoice::where('description', 'New Invoice')->where('processing_type', config('const.type.auto_pay'))->get();
//
//        foreach($invoices as $invoice){
//            $customer = $invoice->customer;
////            dd([$invoice, $customer]);
////            $customer = Customer::find(4667);
//            $emailInfo = ['fromName'    => 'SilverIP Customer Care',
//                          'fromAddress' => 'help@silverip.com',
//                          'toName'      => $customer->first_name . ' ' . $customer->last_name,
//                          'toAddress'   => $customer->email,
//                          'subject'     => 'SilverIP Billing VOIDED'];
//
//            $template = 'email.template_customer_notification';
//            $templateData = ['customer' => $customer];
//
//            SendMail::generalEmail($emailInfo, $template, $templateData);
//        }


        dd('done');

        $sipBilling = new SIPBilling();
        $result = $sipBilling->voidTransaction('IP31070255HNDTXXQH'); //'IP31070250SIUWEQYE'); //'IP01080359GQIJMFGW');
        dd($result);


        $nowMysql = date("Y-m-d H:i:s");
        $invoices = Invoice::where('status', config('const.invoice_status.pending'))
            ->where('processing_type', config('const.type.auto_pay'))
            ->where(function ($query) use ($nowMysql) {
                $query->where('due_date', 'is', 'NULL')
                    ->orWhere('due_date', '<=', $nowMysql)
                    ->orWhere('due_date', '');
            })
            ->take(5)
            ->get();

//        $queries = DB::getQueryLog();
//        $last_query = end($queries);
//        dd($last_query);

        dd($invoices->pluck('processing_type'));


        $building = Building::find(1012);

        $products = null;
        $building->load(['activeBuildingProducts.product' => function ($q) use (&$products) {
            $products = $q->with('propertyValues')->get(); //->unique();
        }]);

        // Filter out everythig except Internet products
        $products = $products->where('id_types', config('const.type.internet'))
            ->whereInLoose('frequency', ['included', 'monthly', 'annual']);

        dd($products);
        dd($building->activeBuildingProducts);

//        $switchIp = '10.11.123.27';
//        $skipLabelPattern = ['/.*[uU]plink.*/i', '/.*[dD]ownlink.*/i'];
//        $sipNetwork = new SIPNetwork();
//        dd($sipNetwork->getSwitchPortInfoTable($switchIp, $skipLabelPattern));
//
//
//
//        $ciscoSwitch = new CiscoSwitch(['readCommunity'  => 'oomoomee',
//                                        'writeCommunity' => 'BigSeem']);
//
//        $portTypeRegEx = '/.*ethernet.*/i';
//        $skipLabelPattern = ['/.*[uU]plink.*/i', '/.*[dD]ownlink.*/i'];
//        $portLabels = $ciscoSwitch->getSnmpAllPortLabel('10.11.123.27', $portTypeRegEx, $skipLabelPattern);
//        dd($portLabels);

//        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
//        dd($mikrotiks);

//        $serverIp = '108.160.193.70';
//        foreach ($mikrotiks as $mikrotik)
//        {
//            $serviceRouter = new MtikRouter(['ip_address' => $mikrotik->ip_address,
//                                             'username' => config('netmgmt.mikrotik.username'),
//                                             'password' => config('netmgmt.mikrotik.password')]);
//            $serviceRouter->updateHotspotServerTarget($mikrotik->ip_address, $serverIp);
//            echo 'Updated ' . $mikrotik->host_name . '<br>';
////            dd('done');
//        }
//        dd('done');

////        $mikrotiks = NetworkNode::where('id', 1251)->get();
//        $mikrotiks = NetworkNode::where('id_types', config('const.type.router'))->get();
////        dd($mikrotiks);
//
//        $loginFileContents = Storage::disk('local')->get('login.html');
//
//        foreach ($mikrotiks as $mikrotik)
//        {
//            config(['filesystems.disks.ftp.host' => $mikrotik->ip_address]);
//            config(['filesystems.disks.ftp.username' => 'admin']);
//            config(['filesystems.disks.ftp.password' => 'BigSeem']);
//
//            $loginFileExists = Storage::disk('ftp')->exists('hotspot/login.html');
//            if ($loginFileExists)
//            {
//                config(['filesystems.disks.ftp.host' => $mikrotik->ip_address]);
//                config(['filesystems.disks.ftp.username' => 'admin']);
//                config(['filesystems.disks.ftp.password' => 'BigSeem']);
//                Storage::disk('ftp')->put('hotspot/login.html', $loginFileContents);
//                echo 'Updated ' . $mikrotik->host_name . '<br>';
//                continue;
////                dd('Updated ' . $mikrotik->host_name);
//            }
//            echo 'Skipped ' . $mikrotik->host_name . '<br>';
////            dd('Skipped ' . $mikrotik->host_name);
//        }

        dd('done');
//        $exists = Storage::disk('local')->exists('login.html');
//
//        config(['filesystems.disks.ftp.host' => '10.10.13.1']);
//        config(['filesystems.disks.ftp.username' => 'admin']);
//        config(['filesystems.disks.ftp.password' => 'BigSeem']);
//
//        $exists = Storage::disk('ftp')->exists('hotspot/login.html');
//
//        dd($exists);
//
//        $exists = Storage::disk('s3')->exists('file.jpg');
//
//        $contents = Storage::get('file.jpg');
//
//        Storage::put(
//            'avatars/' . $user->id,
//            file_get_contents($request->file('avatar')->getRealPath())
//        );
//
//        Storage::copy('old/file1.jpg', 'new/file1.jpg');

//        $allBuildings = Building::orderBy('alias', 'asc')->get();
//        $filteredList = $allBuildings->filter(function ($value, $key) {
//            dd([$key, $value]);
//            return $value > 2;
//        });
//
//dd('done');
//
//        $pm = PaymentMethod::find(14331);
//        dd(json_decode($pm->properties, true));
//

        $building = Building::find(1012);
        $unitNumberMap = $building->getUnitNumbers();


        $addressCollection = Address::where('id_buildings', 1012)
            ->whereNull('id_customers')
            ->get();

        dd([$addressCollection->pluck('address', 'id'), $unitNumberMap]);


        $building = Building::find(68);
        dd($building->properties->pluck('id_building_properties'));

        dd($building->activeInternetProducts);

        $allProducts = Building::find(30)
            ->activeParentProducts()
            ->pluck('product')
            ->toArray();
//dd($allProducts);

        $subbedProducts = Customer::join('customer_products', 'customer_products.id_customers', '=', 'customers.id')
            ->join('address', 'address.id_customers', '=', 'customers.id')
            ->join('products', 'customer_products.id_products', '=', 'products.id')
            ->where('products.id_types', '=', config('const.type.internet'))
            ->where('customer_products.id_status', '=', config('const.status.active'))
            ->where('customers.id_status', '=', config('const.status.active'))
            ->where('address.code', '=', '1300S')
            ->select(DB::raw('count(customers.id) as Total'),
                'products.id',
                'products.name',
                'products.amount',
                'products.frequency')
            ->groupby('products.name')
            ->get();
        dd($subbedProducts);

        foreach ($subbedProducts as $product)
        {
            $subbedProductsArr[$product->id] = $product;
        }
        dd($subbedProductsArr);
        foreach ($allProducts as $key => $product)
        {
            if (array_key_exists($key, $subbedProductsArr))
            {
                $allProducts[$key]['Total'] = $subbedProductsArr[$key]->Total;
            } else
            {
                $allProducts[$key]['Total'] = 0;
            }
        }

        dd($subbedProducts);


        $building = Building::find(28);

        dd($building->allAddresses);


        $lastTicketId = Ticket::max('id');
        $lastTicketNumber = Ticket::find($lastTicketId)->ticket_number;

        dd($lastTicketNumber);

        $ticketNumber = explode('ST-', $lastTicketNumber);
        $ticketNumberCast = (int) $ticketNumber[1] + 1;
        $defaultUserId = 10;

        $newTicket = new Ticket;

        // comment=Test+3&id_customers=4667&id_reasons=13&status=escalated
        $newTicket->id_customers = 4667;
        $newTicket->ticket_number = 'ST-' . $ticketNumberCast;
        $newTicket->id_reasons = 13;
        $newTicket->comment = 'Test 4';
        $newTicket->status = 'escalated';
        $newTicket->id_users = Auth::user()->id;
        $newTicket->id_users_assigned = $defaultUserId;
        $newTicket->save();

        dd($newTicket);


        $sipNetwork = new SIPNetwork();

        $portInfoTable = $sipNetwork->getSwitchPortInfoTable('10.11.254.140');
        $neighborInfoTable = $sipNetwork->getSwitchCdpNeighborInfoTable('10.11.254.140');

        dd([$portInfoTable, $neighborInfoTable]);


        $port = Port::find(5237);
        dd($port->customers);


        $customer = Customer::find(13897);
        dd($customer->ports);


        $building = Building::find(81);

        $address = $building->address->first();

        $singupUtil = new SIPSignup();
        $data = $singupUtil->getBuildingActivationFees($address->id);

        dd($data);


        $myProductPropertyValues = $building->load('buildingProducts.product.propertyValues');

        dd($myProductPropertyValues->buildingProducts->pluck('product')); //->pluck('propertyValues'));

        $products;
        $building->load(['buildingProducts.product' => function ($q) use (&$products) {
            $products = $q->with('propertyValues')->get(); //->unique();
        }]);

        dd($products);

        //
        //
        //        $testArray = array(
        //            '13253' => ['101','102','103','104','105','106','107','108','114','115','116','117','118','119','120','121','122','123','124','201','202','203','204','205','206','207','209','210','214','215','216','217','218','219','220','221','222','223','224','225','226','301','302','303','304','305','306','311','312','313','314','315','316','317','318','319','320','321','322','323','324','325','326','401','402','403','404','405','406','414','415','416','417','418','419','420','421','422','423','424','425','426','501','502','503','504','505','506','514','515','516','517','518','519','520','521','522','523','524','525','526','614','615','616','617','618','619','620','621','622','623','624','625','626','627','714','715','720','721','722','727'],
        //            '13420' => ['101','102','103','104','105','106','107','108','114','115','116','117','118','119','120','121','122','123','124','201','202','203','204','205','206','207','209','210','214','215','216','217','218','219','220','221','222','223','224','225','226','301','302','303','304','305','306','311','312','313','314','315','316','317','318','319','320','321','322','323','324','325','326','401','402','403','404','405','406','414','415','416','417','418','419','420','421','422','423','424','425','426','501','502','503','504','505','506','514','515','516','517','518','519','520','521','522','523','524','525','526','614','615','616','617','618','619','620','621','622','623','624','625','626','627','714','715','720','721','722','727']);
        //
        //        dd(json_encode($testArray));
        //
        //
        //        $switch = new CiscoSwitch(['readCommunity' => config('netmgmt.cisco.read'),
        //                                   'writeCommunity' => config('netmgmt.cisco.write')]);


        //        error_reporting(0);
        //        track_errors(true);

        //        dd($switch->getSnmpModelNumber('10.10.35.6'));
        //        $response = $switch->getSnmpPortOperStatus('10.10.35.6', 11);
        //        $response = $switch->getSnmpAllPortLabel('10.10.35.6', '/Ethernet/');
        //        $response = $switch->getSnmpSysName('10.10.35.6');
        //        $response = $switch->getSnmpAllPortAdminStatus()'10.10.35.6');
        //        $response = $switch->getSnmpAllPortDesc('10.10.35.6', '/Ethernet/');
        //        $response = $switch->getSnmpAllPortAdminStatus('10.10.35.6', '/Ethernet/');

        //        dd($switch->getSnmpPortOperStatus('10.10.35.6', 2));
        //        dd($switch->getSnmpPortSpeed('10.10.35.6', 47));
        //        $response = $switch->getSnmpPortIndex('10.10.35.6', 2);
        //        echo 'Round 1 ... <br>';
        //        $response = $switch->getSnmpPortIndexList('10.10.35.6');
        //        echo 'Round 2 ... <br>';
        //        dd($switch->getSnmpPortIndexList('10.10.35.6'));
        //        $response = $switch->getSnmpPortLastChange('10.10.35.6', '47');
        //        $response = $switch->getSnmpPortfastStatus('10.10.35.6', '48');


        //        dd($switch->getSnmpPortfastMode('10.10.35.6', '6'));

        //        dd($switch->getSnmpSwitchportMode('10.10.35.6', '48'));

        //        $str = "1";
        //        dd(str_pad($str,4,"0",STR_PAD_LEFT));
        //        dd(base_convert('7', 16, 2));
        //        dd($switch->getSnmpPortVlanAssignment('10.10.35.6', '48'));
        //        dd($switch->getBridgePortIndex('10.10.35.6', '26'));

        //        dd($switch->getSnmpTrunkPortNativeVlanAssignment('10.10.35.6', '47'));
        //        dd($switch->getSnmpTrunkPortEncapsulation('10.10.35.6', '47'));
        //        dd($switch->getSnmpBpduGuardStatus('10.10.35.6', '47'));

        //        dd($switch->snmp2_set('10.10.35.6', 'BigSeem', '1.3.6.1.4.1.9.9.68.1.2.2.1.2.10115', 'i', 1));
        //        dd($switch->setSnmpIndexValueByPort('10.10.35.6', '15', false, false, '1.3.6.1.4.1.9.9.68.1.2.2.1.2', 'i', 1));

        //        dd($switch->getSnmpPortInDataOct('10.10.35.6', '10136', true));
        //        dd($switch->getSnmpPortStats('10.10.35.6', '47'));
        //
        //        dd($response);


        //        $port = Port::find(8734);
        //        dd([$port, $port->customer, $port->networkNode]);
        //
        //        $networkNode = $port->networkNode;
        //        dd($networkNode);
        //        dd($networkNode->masterRouter);

        $customer = Customer::find(5380);
        dd($customer->port);

        dd($customer->getNetworkInfo());


        $customer = Customer::find(1928); // David Ellis - 41E8  #1305
        //        dd($customer);
        dd($customer->getNetworkInfo()->toArray());

    }

}
