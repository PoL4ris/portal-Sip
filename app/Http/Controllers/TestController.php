<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Extensions\BillingHelper;
use DB;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\CustomerProduct;
use App\Models\Address;
use App\Models\BillingTransactionLog;
use App\Models\Building\Building;
use App\Models\Product;
use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use App\Http\Controllers\CustomerController;
//use ActivityLogs;

class TestController extends Controller
{
    public function __construct(){
        //        $this->middleware('auth');
        DB::connection()->enableQueryLog();
    }

    public function testCustomerTickets(){
        //        $customer = Customer::with('tickets')
        //                            ->find('501');    
        //        dd($customer);

        $customer = new Customer;
        $tickets = $customer->getTickets('501');
        dd($tickets->toArray());

    }

    public function testCC(){

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

        dd($result);
    }

    public function testDBRelations(){

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

    public function supportTest()
    {




        $supController = new SupportController();


        $record = Ticket::with('customer', 'reason', 'ticketNote','ticketHistory', 'user', 'userAssigned', 'address', 'contacts')
            ->where('id_reasons','!=', 11)
            ->where('status','!=', 'closed')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get()->toArray();

        $result = $supController->getOldTimeTicket($record);


        //    $result = Customer::with('contacts.types')
        $result = Customer::with('addresses', 'contacts', 'type','address.buildings', 'address.buildings.neighborhood', 'status', 'status.type', 'openTickets', 'log')
            //                             'status.type',
            //                             'openTickets')
            ->find(501)
            ->toArray();



        //      print '<pre>';
        dd($result);
        die();


    }

    public function cleanView(){
      $supController = new SupportController();


      $record = Ticket::with('customer', 'reason', 'ticketNote','ticketHistory', 'user', 'userAssigned', 'address', 'contacts')
        ->where('id_reasons','!=', 11)
        ->where('status','!=', 'closed')
        ->orderBy('updated_at', 'desc')
        ->limit(3)
        ->get()->toArray();

      $result = $supController->getOldTimeTicket($record);


      //    $result = Customer::with('contacts.types')
      $result = Customer::with('addresses', 'contacts', 'type','address.buildings', 'address.buildings.neighborhood', 'status', 'status.type', 'openTickets', 'log')
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

      $coso = CustomerProduct::where('id_customers',501)->get()->toArray();

      print_r($coso);
      die();

      $coso = Customer::with('address', 'contact', 'type','address.buildings', 'address.buildings.neighborhood')->find(13579)->toarray();

      $queries = DB::getQueryLog();
      $last_query = end($queries);


      //    print_r($last_query);


      print '----------------------------------------------<br>';

      print_r(
        $coso
      );



      die();
    }

    public function logFunction() {

      $a = 4000000;
//      $a = 63245986;
      $b = 0;

      $x = 1;
      $y = 0;
      $z = 0;
      $p = 0;

      for($p = 0; $z < $a; $p++){


//        print 'SERIE-->' . $p . '<br>';
        $z = $x + $y;
        $y = $x;
        $x = $z;

        if($z % 2 == 0)
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

    public function invoiceTest(){
//        $customerModel = new Customer;
//        dd($customerModel->getActiveCustomerProductsByBuildingID('28'));
//        dd($customerModel->getActiveCustomerProductsByCustomerID('3839'));
//        dd($customerModel->getInvoiceableCustomerProducts(null, '28'));
        
//        $customer = Customer::with('payment')->find(3818);
//        dd($customer);
        
//        dd($billingHelper->getMode());        
        $billingHelper = new BillingHelper();
        dd($billingHelper->generateResidentialInvoiceRecords());
//        $billingHelper->processAutopayInvoices();
    }

    public function testActivityLog(){
        ActivityLog::test();
    }

}
