<?php

namespace App\Http\Controllers;

use App\Models\Support\Adminaccess;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Customer\Customers;
use App\Models\Customer;
use App\Models\TicketNote;
use App\Models\Reason;
use App\Models\CustomerProduct;
use App\Models\Ticket;
use App\Models\Address;
use App\Models\Contact;
use App\Models\BillingTransactionLog;
use App\Models\Building\Servicelocation;
use App\Models\Building\Building;
use App\Models\Network\networkNodes;
use App\Models\Support\Ticketreasons;
use DB;
use Schema;
use Auth;
use App\Http\Controllers\NetworkController;

class CustomerController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }
//  public function dashboard()
//  {
//    return view('customer.dashboard');
//  }
  public function getCustomersSearch(Request $request)
  {
    if($request->ajax())
    {

      $data = $request['querySearch'];

      if(empty($data))
        return;

        $txt = '';
        $complex = '';

        if ($data[0] == 'complex')
        {
          $txt = $data;
          $complex = true;
        }
        else
        {
          for ($x = 0; $x <= sizeof($data)-1; $x ++)
            $txt .= $data[$x] . ' ';
        }

      $customersData = $this->prepareQuery($txt, $complex);

      return json_encode($customersData);
    }
    return "ERROR:";

  }
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

  }
  public function customersData(Request $request)
  {
    return Customer::with('address', 'contacts', 'type')->find($request->id);
  }
  public function getCustomerPayment(Request $request)
  {
    return Customer::with('payment', 'type')->find($request->id);
  }
  public function getNewTicketData(Request $request)
  {
    return Customer::with('tickets')->find($request->id);
  }
  public function getTicketHistory(Request $request)
  {
    return Customer::with('ticketHistory')->find($request->id);
  }
  public function getTicketHistoryNotes(Request $request)
  {
    return TicketNote::find($request->id);
  }
  public function getTicketHistoryReason(Request $request)
  {
    return Reason::find($request->id);
  }
  public function getBillingHistory(Request $request)
  {
    return DB::Select('select * from billingTransactionLog where CID = ' . $request->id);
  }
  public function getCustomerServices(Request $request)
  {
    return Customer::with('services')->find($request->id);
  }
  public function getCustomerProduct(Request $request)
  {
    return CustomerProduct::with('product')->find($request->id);
  }
  public function getCustomerProductType(Request $request)
  {
    return CustomerProduct::with('status')->find($request->id);
  }
  public function getCustomerBuilding(Request $request)
  {

    print '<pre>';
    print_r(Building::find($request->id)->toArray());
    die();




    return CustomerProduct::with('status')->find($request->id);
  }
  public function getCustomerNetwork(Request $request)
  {
    $port = $this->getPortID($request->id);
    $networkControllerInfo = new NetworkController();
    $networkData = $networkControllerInfo->getCustomerConnectionInfo($port);
    $networkData['portId'] = $port;
    return $networkData;
  }
  public function getPortID($id)
  {
    return CustomerProduct::with('port')->where('id_customers', $id)->get()[0]->id;
  }
  public function getCustomerList ()
  {
    return Customer::all()->take(100);
  }


  public function updateAddressTable(Request $request)
  {
    $data = $request->all();
    unset($data['id_customers']);
    Address::where('id_customers', $request->id_customers)->update($data);
    return 'OK';
  }
  public function updateCustomersTable(Request $request)
  {
    $data = $request->all();
    unset($data['id_customers']);
    Customer::where('id', $request->id_customers)->update($data);
    return 'OK';
  }
  public function updateContactsTable(Request $request)
  {
    $data = $request->all();
    unset($data['id_customers']);
    Contact::where('id_customers', $request->id_customers)->update($data);
    return 'OK';
  }
  public function getCustomerDataTicket(Request $request)
  {
    return Customer::find($request->id);
  }











  public function customers(Request $request)
  {
    if ($request->id)
      $customer = $this->getCustomerData($request->id);
    else
      $customer = Customers::orderBy('CID', 'desc')->get();

    return view('customer.customers', ['customer' => $customer]);
  }
  public function getCustomerData($id)
  {
    $networkControllerInfo = new NetworkController();
    $customer['customer']           = Customers::where('CID', $id)->first();
    $customer['building']           = Servicelocation::where('LocID', $customer['customer']->LocID)->first();
    $customer['billing']            = billingTransactionLog::where('CID', $customer['customer']->CID)->get();
    $customer['network']            = $networkControllerInfo->getCustomerConnectionInfo($customer['customer']->PortID);
    $customer['ticketreasone']      = Ticketreasons::get();
    $customer['tickethistory']      = DB::select('SELECT st.CID, st.RID, st.TicketNumber, str.ReasonShortDesc, st.DateCreated, st.Comment, st.Status, st.LastUpdate 
                                                    FROM supportTickets st 
                                                      INNER JOIN supportTicketReasons str 
                                                        ON st.RID = str.RID 
                                                        WHERE  st.CID = ' . $id);

    $customer['services']           = DB::select('SELECT p.ProdName, p.Amount, p.ChargeFrequency, cp.Status , p.ProdType, p.ProdID, cp.CSID
                                                    FROM customerProducts cp 
                                                      INNER JOIN products p 
                                                        ON cp.ProdID = p.ProdID 
                                                        WHERE cp.CID = ' . $id);

    $customer['addservices']        = DB::select('SELECT pr.ProdID, pr.ProdName, pr.Amount, pr.ChargeFrequency, cu.DateSignup, cu.DateRenewed, slp.Status, cu.DateExpires, pr.ProdComments
                                                    FROM serviceLocationProducts slp
                                                      INNER JOIN customers cu 
                                                        ON cu.LocID = slp.LocID
                                                      INNER JOIN products pr 
                                                        ON pr.ProdID = slp.ProdID
                                                        WHERE  cu.CID = ' . $id . ' 
                                                          AND pr.ParentProdID = 0');

    if(sizeof($customer['services']) != 0 )
    {
      foreach ($customer['services'] as $servicess)
      {
        $valuetemp[$servicess->ProdType] = DB::select('SELECT pr.ProdID, pr.ProdType, pr.ProdName, pr.Amount, pr.ChargeFrequency, cu.DateSignup, cu.DateRenewed, slp.Status, cu.DateExpires, pr.ProdComments,cp.CProdDateExpires,cp.CProdDateUpdated, cp.CSID
                                                        FROM serviceLocationProducts slp
                                                          INNER JOIN customers cu 
                                                            ON cu.LocID = slp.LocID
                                                          INNER JOIN products pr 
                                                            ON pr.ProdID = slp.ProdID
                                                          INNER JOIN customerProducts cp 
                                                            ON cp.ProdID = pr.ProdID
                                                            WHERE  cu.CID = '. $id .'
                                                              AND pr.ParentProdID = 0
                                                                AND pr.ProdType = "' .$servicess->ProdType .'"
                                                                  GROUP BY pr.ProdID');
      }
      $customer['servicesactiveinfo'] = $valuetemp;
    }
    else
      $customer['servicesactiveinfo'] = null;

    return $customer;

  }
  public function updateCustomerData(Request $request)
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
  public function insertCustomerData(Request $request)
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
  public function insertCustomerTicket(Request $request)
  {
    $data = $request->all();
    $comment = $data['comment'];
    unset($data['comment']);

    $lastTicketNumber = Ticket::all()->last()->ticket_number;
    $ticketNumber = explode('ST-',$lastTicketNumber);
    $ticketNumberCast = (int)$ticketNumber[1] + 1;

    $data['ticket_number'] = 'ST-' . $ticketNumberCast;
    $data['id_users'] = Auth::user()->id;

    DB::table('ticket_notes')->insert(['comment'=>$comment, 'id_users'=>$data['id_users']]);
    $ticketNoteId = TicketNote::all()->last()->id;

    $data['id_ticket_notes'] = $ticketNoteId;
    //Default User
    $data['id_users_assigned'] = 10;

    DB::table('tickets')->insert($data);

    return 'OK';
  }
  public function updateCustomerServiceInfo(Request $request)
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
  public function updateCustomerActiveServiceInfo(Request $request)
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
}
