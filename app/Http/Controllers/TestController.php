<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
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
        dd($tickets);
        
    }
    
    public function testCC(){

        //        $customer = Customers::find('10248');
        $customer = Customers::find('10249');
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

    //$ticket2 = Customer::with('type', 'status', 'contacts')->find(1783)->toArray();
    //dd(Customer::with('ticketHistory')->find(1783));
    //dd(Ticket::with('address')->find(18685));








    dd(
        Building::where('name',       'LIKE', '%tower%')
                ->orWhere('alias',    'LIKE', '%tower%')
                ->orWhere('nickname', 'LIKE', '%tower%')
                ->orWhere('code',     'LIKE', '%tower%')
                ->orWhere('units',    'LIKE', '%tower%')
                ->get()
                ->toArray()
      );


//dd(
//
//);







//1783
//18685

//        $queries = DB::getQueryLog();
//        $last_query = end($queries);
//        dd($last_query);

//        dd($ticket2);
  }

  public function cleanView(){
    return;
    }
}
