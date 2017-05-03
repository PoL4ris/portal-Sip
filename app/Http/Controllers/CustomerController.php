<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Http\Request;
//Models
use App\Models\Note;
use App\Models\Product;
use App\Models\Support\Adminaccess;
use App\Models\PaymentMethod;
use App\Models\Customer\Customers;
use App\Models\Customer;
use App\Models\TicketNote;
use App\Models\Reason;
use App\Models\CustomerProduct;
use App\Models\Ticket;
use App\Models\Address;
use App\Models\Contact;
use App\Models\BillingTransactionLog;
use App\Models\Invoice;
use App\Models\Building;
use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\Support\Ticketreasons;
use App\Models\ActivityLog;

use App\Http\Controllers\NetworkController;

use DB;
use Log;
use Schema;
use Auth;
use ActivityLogs;
use SendMail;

class CustomerController extends Controller
{
    protected $logType;

    public function __construct() {
        $this->middleware('auth');
        $this->logType = 'customer';
    }
    public function getCustomersSearch(Request $request)
    {
        $string = $request->querySearch;
        $select = "select * from address inner join customers on address.id_customers = customers.id ";
        $limit = ' limit 50';
        $arrX = array();
        $arrY = ' ';
        $whereFlag = false;
        $pattern = '/([0-9])\w+/';
        $stringArray = explode(' ', $string);

        foreach($stringArray as $index => $item)
        {
            preg_match($pattern, $item, $patternItemResult);

            if(count($patternItemResult) == 0)
                $arrX[$index] = " AND ( address.code like '%" . $item . "%' or address.unit like '%" . $item . "%' or customers.first_name like '%" . $item . "%' or customers.last_name like '%" . $item . "%' or customers.email like '%" . $item . "%')";
            else
            {
                if($whereFlag)
                    $arrX['where'] .= " AND (address.code like '%" .  $patternItemResult[0] . "%' OR address.unit like '%". $patternItemResult[0] . "%') ";
                else
                {
                    $whereFlag = true;
                    $arrX['where'] = " where (address.code like '%" .  $patternItemResult[0] . "%' OR address.unit like '%". $patternItemResult[0] . "%') ";
                }
            }
        }

        if($whereFlag)
        {
            $tmpWhere = $arrX['where'];
            unset($arrX['where']);
        }

        $arrY .= $whereFlag?$tmpWhere:'';

        foreach($arrX as $idx => $or)
            $arrY .= $or;

        return DB::select($select . $arrY . $limit);
    }//MAIN SEARCH NEEDS TO RENAME

    public function prepareQuery($data, $complex = null)
    {

        $tmpQueryData = $defaultData = $token = '';

        if(!$complex)
        {
            $cleanData =  explode(' ', $data);

            if(sizeof($cleanData) >= 1)
                $defaultData = 'building.ShortName LIKE "%' . $cleanData[0] . '%" AND customers.Unit LIKE "%' . $cleanData[1] . '%" ';
            else
                $defaultData = 'building.ShortName LIKE "%' . $cleanData[0] . '%" ';
            //    $defaultData = 'building.address LIKE "%' . $cleanData[0] . '%"';
            //BUILDING TABLE

            $tmpQueryData = $defaultData;
        }
        else
        {
            $complexQuery = [1 => 'Tel', 2 => 'Email', 3 => 'Unit', 4 => 'ShortName'];
            foreach ($data as $x => $tipoQuery)
            {
                if($x == 0)
                    continue;

                if ($x == 4)
                    $tabla = 'building';
                else
                    $tabla = 'customers';

                if(!empty($tipoQuery))
                {
                    if($token != '')
                    {
                        $tmpQueryData .= ' AND ' . $tabla . '.' . $complexQuery[$x] . ' LIKE "%' . $tipoQuery .'%" ';
                    }
                    else
                    {
                        $tmpQueryData = '' . $tabla . '.' . $complexQuery[$x] . ' LIKE "%' . $tipoQuery .'%" ';
                        $token = 'up';
                        $defaultData = null;
                    }
                }
            }
        }

        if (empty($tmpQueryData))
            return;

        //return  DB::select('select * from building inner join customers on building.id = customers.CID where ' . ($tmpQueryData?$tmpQueryData:$defaultData));
        //BUILDING TABLE
        return  DB::select('SELECT * FROM serviceLocation building INNER JOIN customers ON building.LocID = customers.LocID WHERE ' . ($tmpQueryData?$tmpQueryData:$defaultData) . ' LIMIT 100 ');

    }//NO

    public function customersData(Request $request)
    {

        return Customer::with('addresses',
                              'contacts',
                              'type',
                              'address.buildings',
                              'address.buildings.neighborhood',
                              'status',
                              'status.type',
                              'openTickets',
                              'log',
                              'log.user')
            ->find($request->id);
    }//MAIN FUNCTION CUSTOMER NEED TO RENAME

    public function getCustomerStatus(Request $request)
    {
        return Customer::with('status')->find($request->id)['status'];
    }//SI
    public function insertCustomerNote(Request $request){

        $note = new Note;
        $note->comment = $request->note;
        $note->created_by = Auth::user()->id;
        $note->id_customers = $request->id;
        $note->save();

        return Note::where('id_customers', $request->id)->get();
    }
    public function getCustomerNotes(Request $request){
        return Note::where('id_customers', $request->id)->get();
    }
    public function resetCustomerPassword(Request $request){

        $customer = Customer::with('contact')->find($request->id);

        $match = preg_split('/[^0-9]+/', $customer->contact->value);

        foreach($match as $item){
            if(isset($result))
                $result .= $item;
            else
                $result = $item;
        }

        $customer->password = bcrypt($result);
        $customer->save();

        //ADD ACTIVITY LOG HERE
        return ['response' => 'OK', 'password' => $result];

    }
    public function getCustomerContactData(Request $request)
    {
        return Customer::with('contacts')->find($request->id);
    }//SI
    public function getContactTypes()
    {
        return ContactType::get();
    }//SI

    public function getDefaultPaymentMethod(Request $request) {

        $customer = Customer::find($request->id);
        return ($customer->defaultPaymentMethod != null) ? [$customer->defaultPaymentMethod, $customer->defaultPaymentMethod->getProperties()] : [];
        //    return Customer::with('payment')->getRelation('payment')->where('priority', 1)->where('id_customers', $request->id)->get();
    }

    public function getAllPaymentMethods(Request $request) {

        $customer = Customer::find($request->id);
        return $customer->allPaymentMethods;
    }//SI

    public function setDefaultPaymentMethod(Request $request) {

        $customer = Customer::find($request->customerID);
        $oldDefaultPm = $customer->defaultPaymentMethod;

        // Set new default payment method
        PaymentMethod::where('id', $request->id)->update(['priority' => 1]);

        // Deactivate other payment methods
        PaymentMethod::where('id_customers', $request->customerID)
            ->where('id', '!=', $request->id)
            ->update(['priority' => 0]);

        $newDefaultPm = $customer->defaultPaymentMethod;

        $data = $newDefaultPm->getProperty('last four');

        $newData = array();
        $newData['priority'] = 1;

        ActivityLogs::add($this->logType, $request->customerID, 'update', 'updatePaymentMethods', $oldDefaultPm, $newDefaultPm, $data, ('update-payment'));


        return $customer->allPaymentMethods;

    }//SI

    public function getNewTicketData(Request $request)
    {
        return Customer::with('tickets')->find($request->id);
    }//SI
    public function getTicketHistory(Request $request)
    {
        $customer = new Customer;
        return $customer->getTickets($request->id);
    }//SI
    public function getTicketHistoryNotes(Request $request)
    {
        return TicketNote::find($request->id);
    }//SI
    public function getTicketHistoryReason(Request $request)
    {
        return Reason::find($request->id);
    }//SI
    public function getInvoiceHistory(Request $request)
    {
        return Invoice::where('id_customers', $request->id)->get();
    }//SI
    public function getBillingHistory(Request $request)
    {
        return billingTransactionLog::where('id_customers', $request->id)->get();
    }//SI
    public function getCustomerServices(Request $request)//SI
    {
        return Customer::with('services')->find($request->id?$request->id:$request->idCustomer);
    }
    public function getCustomerProduct(Request $request)//SI
    {
        return CustomerProduct::with('product')->find($request->id);
    }
    public function getCustomerProductType(Request $request)//SI
    {
        return CustomerProduct::with('status')->find($request->id);
    }
    public function getCustomerBuilding(Request $request)//SI
    {

        print '<pre>';
        print_r(Building::find($request->id)->toArray());
        die();




        return CustomerProduct::with('status')->find($request->id);
    }
    public function getCustomerNetwork(Request $request)//SI
    {

        $customer = new Customer;
        $customer = Customer::find($request->id);
        //    return $customer->getNetworkNodes($request->id);
        $netInfo = $customer->getNetworkInfo();
        //      Log::info($netInfo);
        return $netInfo;
        //
        //    return NetworkNode::join('ports', 'ports.id_network_nodes', '=', 'network_nodes.id')
        //                      ->join('customers', 'ports.id_customers', '=', 'customers.id')
        //                      ->where('ports.id_customers', '=', $request->id)
        //                      ->select('*')
        //                      ->get();

        //
        //    print_r($warp);die();
        //
        //    $data = $request->all();
        //
        //    $port = $this->getPortID($request->id);
        //
        //    $networkControllerInfo = new NetworkController();
        //    $networkData = $networkControllerInfo->getCustomerConnectionInfo($port);
        //    $networkData['portId'] = $port;
        //    return $networkData;

    }
    public function getPortID($id)//SI
    {
        return CustomerProduct::with('port')->where('id_customers', $id)->get()[0]->id;
    }
    public function getCustomerList ()//SI
    {
        return Ticket::with('customer', 'address')->orderBy('created_at', 'asc')->where('id_customers', '!=', 1)->groupBy('id_customers')->take(100)->get();
        return Customer::all()->take(100);
    }
    public function getAddress()//SI
    {
        return Address::groupBy('id_buildings')->get();
    }

    public function updateAddressTable(Request $request)//SI
    {

        $newData = array();
        $hasHashtag = explode('#', $request->value);

        $newData[$request->field] = (count($hasHashtag) == 1) ? $hasHashtag[0] : $hasHashtag[1];

        $addressExist = Address::find($request->id_table);
        $addressExist->unit = (count($hasHashtag) == 1) ? $hasHashtag[0] : $hasHashtag[1];
        $addressExist->save();

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateAddressTable', $addressExist, $newData, null, 'update-unit');

        return 'OK';


        //RECHECK
        //    $params = $request->all();
        //    $data[$params['field']] = explode('# ', $params['value'])[1];
        //
        //    $recordCustomer = $data;
        //    $recordCustomer['old_data'] = Address::find($request->id)->toArray();
        //
        //    Address::where('id', $request->id)->update($data);
        //
        //    $logData['id_users'] = Auth::user()->id;
        //    $logData['id_customers'] = $recordCustomer['old_data']['id_customers'];
        //    $logData['action']   = 'update';
        //    $logData['route']    = 'updateAddressTable';
        //    $logData['data']     = serialize($recordCustomer);
        //
        //    Log::insert($logData);
        //
        //    return 'OK';

    }
    public function updateCustomersTable(Request $request)//SI
    {
        $params = $request->all();
        $newData = array();
        $newData[$params['field']] = $params['value'];

        // Get current customer data
        $currrentData = Customer::find($request->id)->toArray();

        // Update customer data
        Customer::where('id', $request->id)->update($newData);

        // Log this activity
        ActivityLogs::add($this->logType, $request->id, 'update', 'updateCustomersTable', $currrentData, $newData, null, ('update-' . $params['field']));

        return 'OK';
    }
    public function updateContactInfo(Request $request)//SI
    {
        if (empty($request->value))
            return 'ERROR';

        $record = Contact::find($request->id);
        $record->value = $request->value;
        $record->save();
        return 'OK';

    }
    public function updateContactsTable(Request $request)//SI
    {



        $newData = array();
        $newData[$request->field] = $request->value;

        $contactExist = Contact::find($request->id_table);
        $contactExist->value = $request->value;
        $contactExist->save();

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateContactsTable',  $contactExist, $newData, null, 'update-contact');


        return 'OK';

        //RECHECK
        //    if($contactExist)
        //    {
        //
        //      $contactId = Contact::where('id_customers',$request->id_customers)->get()->toArray()[0]['id'];
        //      $contact = Contact::find($contactId);
        //      $contact->value = $request->value;
        //    }
        //    else
        //    {
        //      $data['id_types'] = 2;
        //      $data['created_at'] = date("Y-m-d H:i:s");
        //      $data['updated_at'] = date("Y-m-d H:i:s");
        //      Contact::insert($data);
        //    }
        //
        //    return 'OK';

    }
    public function getCustomerDataTicket(Request $request)//SI
    {
        return Customer::find($request->id);
    }
    public function insertCustomerService(Request $request)//SI
    {

        $when = $this->getTimeToAdd(Product::find($request->idProduct)->frequency);

        $expires = date("Y-m-d H:i:s", strtotime($when));

        $newData = new CustomerProduct();
        $newData->id_customers   = $request->idCustomer;
        $newData->id_products    = $request->idProduct;
        $newData->id_status      = 1;
        $newData->signed_up      = date("Y-m-d H:i:s");
        $newData->expires        = $expires;
        $newData->id_users       = Auth::user()->id;
        $newData->save();

        $relationData = Product::find($request->idProduct);

        ActivityLogs::add($this->logType, $request->idCustomer, 'insert', 'insertCustomerService', null, $newData, $relationData, 'insert-service');

        return $this->getCustomerServices($request);

    }
    public function getTimeToAdd($type)//SI
    {

        $timeToAdd = array('annual'    => 'first day of next year',
                           'monthly'   => 'first day of next month',
                           'onetime'   => 'first day of next month',
                           'Included'  => 'first day of next month',
                           'included'  => 'first day of next month'
                          );
        return $timeToAdd[$type];
    }
    public function disableCustomerServices(Request $request)//SI
    {
        /*
   * Status
    1 = active
    2 = disabled
    3 = decommissioned
    4 = pending
    5 = admin
  */

        $activeService = CustomerProduct::find($request->idService);
        $activeService->id_status = 2;
        $activeService->save();

        $newData = array();
        $newData['id_status'] = 2;

        $relationData = Product::find($activeService->id_products);

        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $activeService, $newData, $relationData, 'disable-service');

        return $this->getCustomerServices($request);

    }
    public function activeCustomerServices(Request $request)//SI
    {
        /*
     * Status
      1 = active
      2 = disabled
      3 = decommissioned
      4 = pending
      5 = admin
    */

        $activeService = CustomerProduct::find($request->idService);
        $activeService->id_status = 1;
        $activeService->save();


        $newData = array();
        $newData['id_status'] = 1;

        $relationData = Product::find($activeService->id_products);

        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $activeService, $newData, $relationData, 'active-service');


        return $this->getCustomerServices($request);

    }
    public function updateCustomerServices(Request $request)//SI
    {

        /*
     * ID Customer
     * OldId RecordId
     * NewId to update on record.
     *
     * Status 1 = Active
    */

        $when = $this->getTimeToAdd(Product::find($request->newId)->frequency);
        $expires = date("Y-m-d H:i:s", strtotime('first day of next ' . $when));

        $updateService = CustomerProduct::find($request->oldId);
        $updateService->id_products = $request->newId;
        $updateService->signed_up   = date("Y-m-d H:i:s");
        $updateService->expires     = $expires;
        $updateService->id_users    = Auth::user()->id;
        $updateService->id_status   = 1;
        $updateService->save();


        $newData = array();
        $newData['id_products'] = $request->newId;

        $relationData = Product::find($request->newId);

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateCustomerServices', $updateService, $newData, $relationData, 'update-service');

        return 'OK';


    }
    public function insertContactInfo(Request $request)//SI
    {

        $data = array('id_customers' => $request->customerId,
                      'id_types'     => $request->typeId,
                      'value'        => $request->contactInfoVal);

        Contact::insert($data);

        return Customer::with('contacts')->find($request->customerId);
    }

    //  public function customers(Request $request)//NO
    //  {
    //    if ($request->id)
    //      $customer = $this->getCustomerData($request->id);
    //    else
    //      $customer = Customers::orderBy('CID', 'desc')->get();
    //
    //    return view('customer.customers', ['customer' => $customer]);
    //  }
    //  public function getCustomerData($id)//NO
    //  {
    //    $networkControllerInfo = new NetworkController();
    //    $customer['customer']           = Customers::where('CID', $id)->first();
    //    $customer['building']           = Servicelocation::where('LocID', $customer['customer']->LocID)->first();
    //    $customer['billing']            = billingTransactionLog::where('CID', $customer['customer']->CID)->get();
    //    $customer['network']            = $networkControllerInfo->getCustomerConnectionInfo($customer['customer']->PortID);
    //    $customer['ticketreasone']      = Ticketreasons::get();
    //    $customer['tickethistory']      = DB::select('SELECT st.CID, st.RID, st.TicketNumber, str.ReasonShortDesc, st.DateCreated, st.Comment, st.Status, st.LastUpdate
    //                                                    FROM supportTickets st
    //                                                      INNER JOIN supportTicketReasons str
    //                                                        ON st.RID = str.RID
    //                                                        WHERE  st.CID = ' . $id);
    //
    //    $customer['services']           = DB::select('SELECT p.ProdName, p.Amount, p.ChargeFrequency, cp.Status , p.ProdType, p.ProdID, cp.CSID
    //                                                    FROM customerProducts cp
    //                                                      INNER JOIN products p
    //                                                        ON cp.ProdID = p.ProdID
    //                                                        WHERE cp.CID = ' . $id);
    //
    //    $customer['addservices']        = DB::select('SELECT pr.ProdID, pr.ProdName, pr.Amount, pr.ChargeFrequency, cu.DateSignup, cu.DateRenewed, slp.Status, cu.DateExpires, pr.ProdComments
    //                                                    FROM serviceLocationProducts slp
    //                                                      INNER JOIN customers cu
    //                                                        ON cu.LocID = slp.LocID
    //                                                      INNER JOIN products pr
    //                                                        ON pr.ProdID = slp.ProdID
    //                                                        WHERE  cu.CID = ' . $id . '
    //                                                          AND pr.ParentProdID = 0');
    //
    //    if(sizeof($customer['services']) != 0 )
    //    {
    //      foreach ($customer['services'] as $servicess)
    //      {
    //        $valuetemp[$servicess->ProdType] = DB::select('SELECT pr.ProdID, pr.ProdType, pr.ProdName, pr.Amount, pr.ChargeFrequency, cu.DateSignup, cu.DateRenewed, slp.Status, cu.DateExpires, pr.ProdComments,cp.CProdDateExpires,cp.CProdDateUpdated, cp.CSID
    //                                                        FROM serviceLocationProducts slp
    //                                                          INNER JOIN customers cu
    //                                                            ON cu.LocID = slp.LocID
    //                                                          INNER JOIN products pr
    //                                                            ON pr.ProdID = slp.ProdID
    //                                                          INNER JOIN customerProducts cp
    //                                                            ON cp.ProdID = pr.ProdID
    //                                                            WHERE  cu.CID = '. $id .'
    //                                                              AND pr.ParentProdID = 0
    //                                                                AND pr.ProdType = "' .$servicess->ProdType .'"
    //                                                                  GROUP BY pr.ProdID');
    //      }
    //      $customer['servicesactiveinfo'] = $valuetemp;
    //    }
    //    else
    //      $customer['servicesactiveinfo'] = null;
    //
    //    return $customer;
    //
    //  }
    public function updateCustomerData(Request $request)//NO
    {
        $idsChart = ['customers' => 'CID', 'supportTicketHistory' => 'TID'];

        if($request->ajax())
        {
            if(!Schema::hasTable($request['table']))
                return "ERROR";

            $data = $request->all();

            unset($data['_token'], $data['id'],$data['table'],$data['bloque']);

            switch ($request['table'])
            {
                case 'customers':
                    DB::table($request['table'])
                        ->where($idsChart[$request['table']], $request['id'])
                        ->update($data);
                    break;
                case 'supportTicketHistory':
                    $data[$idsChart[$request['table']]] = $request['id'];
                    DB::table($request['table'])
                        ->insert($data);
                    $resultData = json_decode(json_encode(DB::Select('SELECT sth.TimeStamp, sth.Comment, sth.Status, aa.Name
                                                              FROM supportTicketHistory sth 
                                                                INNER JOIN AdminAccess aa
                                                                  ON sth.StaffID = aa.ID
                                                                  WHERE sth.TID = ' .$request['id'] . ' 
                                                                  ORDER BY sth.THID DESC 
                                                                  LIMIT 1')), true);
                    break;
            }

            return 'OK';

        }
        return "ERROR:";
    }
    public function insertCustomerData(Request $request)//NO
    {
        if($request->ajax())
        {

            $data = $request->all();
            unset($data['_token']);

            $lastTicketNumber = DB::select('select TicketNumber from supportTickets order by TID desc limit 1')[0]->TicketNumber;
            $ticketNumber = explode('ST-',$lastTicketNumber);
            $ticketNumberCast = (int)$ticketNumber[1] + 1;

            $data['TicketNumber'] = 'ST-' . $ticketNumberCast;

            $staffId = Adminaccess::where ('id_users', Auth::user()->id)->first();

            $data['StaffID'] = $staffId['ID'];

            DB::table('supportTickets')->insert($data);

            return 'OK';
        }

    }
    public function insertCustomerTicket(Request $request)//SI
    {
        //Default User = 0 /10

        $lastTicketId = Ticket::max('id');
        $lastTicketNumber = Ticket::find($lastTicketId)->ticket_number;
        $ticketNumber     = explode('ST-',$lastTicketNumber);
        $ticketNumberCast = (int)$ticketNumber[1] + 1;
        //    $defaultUserId    = 10;

        $newTicket = new Ticket;

        $newTicket->id_customers      = $request->id_customers;
        $newTicket->ticket_number     = 'ST-' . $ticketNumberCast;
        $newTicket->id_reasons        = $request->id_reasons;
        $newTicket->comment           = $request->comment;
        $newTicket->status            = $request->status;
        $newTicket->id_users          = Auth::user()->id;
        //    $newTicket->id_users_assigned = $defaultUserId;
        $newTicket->save();

        //1 = new ticket
        //2 = update ticket
        SendMail::ticketMail($newTicket, 1);
        return 'OK';
    }
    public function updateCustomerServiceInfo(Request $request)//NO
    {
        if($request->ajax())
        {
            if (empty($request->all()))
                return 'ERROR';
            else
                $data = $request->all();

            DB::table('customerProducts')
                ->where('CSID',$data['serviceid'])
                ->update(array('Status' => ($data['status'] == 'active')?'disabled':'active'));

            return 'OK';
        }

    }
    public function updateCustomerActiveServiceInfo(Request $request)//NO
    {
        if($request->ajax())
        {
            if (empty($request->all()))
                return 'ERROR';
            else
                $data = $request->all();

            $staffId = Adminaccess::where ('id_users', Auth::user()->id)->first();



            DB::table('customerProducts')
                ->where('CSID',$data['CSID'])
                ->update(array('ProdID' => $data['ProdID'], 'CProdDateUpdated' => date("Y-m-d H:i:s"), 'UpdatedByID' => $staffId['ID']));

            return DB::select('SELECT PR.Amount amount,  CP.CProdDateSignup signup,PR.ChargeFrequency cycle,  CP.CProdDateRenewed renewed,  CP.Status status,  CP.CProdDateExpires expires,  CP.CProdDateUpdated lastupdate,  CP.Comments comment
                          FROM customerProducts CP 
                            INNER JOIN products PR 
                              ON CP.ProdID = PR.ProdID 
                                WHERE CP.CSID = ' . $data['CSID']) ;
        }

    }

    public function getCustomerLog(Request $request)//SI
    {
        //RECHECK
        return ActivityLog::with('user')
            ->where('type'    ,$request->type)
            ->where('id_type' ,$request->id_type)
            ->orderBy('id'    , 'desc')
            ->get();
    }
}
