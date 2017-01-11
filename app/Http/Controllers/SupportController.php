<?php

namespace App\Http\Controllers;

use App\Models\TicketNote;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Schema;
use Auth;
//use App\Models\Support\Tickethistory;
use App\Models\Support\Ticketreasons;
use App\Models\Support\Adminaccess;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Address;
use App\Models\Product;
use App\Models\Building\Building;

class SupportController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
    DB::connection()->enableQueryLog();

  }
  public function updateTicketCustomerName(Request $request)
  {
    Ticket::where('id', $request->id)->update(['id_customers'=>$request->id_customers]);
    $request['ticketId'] = $request->id;
    return $this->getTicketInfo($request);
  }
  public function getTicketCustomerList(Request $request)
  {
    if ($request->unit == 'false')
      return Address::where('code', 'LIKE','%'.$request->code.'%')->get();
    else if ($request->code == 'false')
      return Address::where('unit', 'LIKE','%'.$request->unit.'%')->get();
    else
      return Address::where('unit', 'LIKE','%'.$request->unit.'%')->where('code', 'LIKE','%'.$request->code.'%')->get();
  }
  public function getTicketInfo(Request $request)
  {
   return Ticket::with('customer', 'address', 'contacts', 'userAssigned', 'reason', 'ticketNote', 'ticketHistoryFull')->find($request['ticketId']);
  }
  public function supportTicketHistory(Request $request)
  {
    return TicketHistory::with('reason', 'user', 'ticketNote')->find($request->id);
  }
  public function supportTickets()
  {
    $record = Ticket::with('customer', 'reason', 'ticketNote','ticketHistory', 'user', 'userAssigned', 'address', 'contacts')
                      ->where('id_reasons','!=', 11)
                      ->where('status','!=', 'closed')
//                      ->where('id',3413)
                      ->orderBy('updated_at', 'desc')
//                      ->limit(1)
                      ->get()->toArray();

    $result = $this->getOldTimeTicket($record);
    return $result;

  }
  public function getOldTimeTicket($record)
  {
    //$new_time   = date("Y-m-d H:i:s");
    $time12     = date("Y-m-d H:i:s", strtotime('-12 hours'));
    $time24     = date("Y-m-d H:i:s", strtotime('-24 hours'));
    $time48     = date("Y-m-d H:i:s", strtotime('-48 hours'));

    foreach($record as $k => $rec)
    {
      if ($rec['updated_at'] <= $time48)
        $record[$k]['old'] = 'old-red';
      else if($rec['updated_at'] <= $time24)
        $record[$k]['old'] = 'old-yellow';
      else if($rec['updated_at'] <= $time12)
        $record[$k]['old'] = 'old-green';
      else
        $record[$k]['old'] = 'old';
    }

    return $record;
  }
  public function getTicketOpenTime()
  {
    $time12     = date("Y-m-d H:i:s", strtotime('-12 hours'));
    $time24     = date("Y-m-d H:i:s", strtotime('-24 hours'));
    $time48     = date("Y-m-d H:i:s", strtotime('-48 hours'));

    $old48 = Ticket::where('updated_at', '<=', $time48)->where('status', '!=', 'closed')->count();
    $old24 = Ticket::where('updated_at', '<=', $time24)->where('updated_at', '>=', $time48)->where('status', '!=', 'closed')->count();
    $old12 = Ticket::where('updated_at', '<=', $time12)->where('updated_at', '>=', $time24)->where('status', '!=', 'closed')->count();
    $old   = Ticket::where('status', '!=', 'closed')->count();

    $result['old48'] = $old48;
    $result['old24'] = $old24;
    $result['old12'] = $old12;
    $result['old'] = $old;
    return $result;

  }
  public function supportTicketsBilling()
  {
    $record =  Ticket::with('customer', 'reason', 'ticketNote','ticketHistory', 'user', 'userAssigned', 'address', 'contacts')
                    ->where('id_reasons', 11)
                    ->orderBy('updated_at', 'desc')
                    ->get();

    $result = $this->getOldTimeTicket($record);
    return $result;
  }
  public function supportTicketsAll()
  {
    $record =  Ticket::with('customer', 'reason', 'ticketNote','ticketHistory', 'user', 'userAssigned', 'address', 'contacts')
                    ->where('status','!=', 'closed')
                    ->orderBy('updated_at', 'desc')
                    ->get();

    $result = $this->getOldTimeTicket($record);
    return $result;
  }
  public function getAvailableServices(Request $request){

    $idList = array();
    $bldID = Customer::with('address')->find($request->id)->address->id_buildings;
    $products = Building::with('products')->find($bldID)->products->toArray();

    foreach($products as $x => $idS)
      $idList[$x] = $idS['id_products'];



    return Product::whereIn('id', $idList)->get();
  }


  public function dashboard(Request $request)
  {

    if ($request->filter)
      $ticketsData = $this->getTicketsData($request->filter);
    else
      $ticketsData = $this->getTicketsData('');

    return $ticketsData;
//    return view('support.dashboard', ['tickets' => $ticketsData]);
  }

  public function getTicketsData($filter)
    {
      $filterQuery = ' AND reasone.ReasonShortDesc != "Billing"';

      if(!empty($filter))
        if ($filter == 'Billing')
          $filterQuery = ' AND reasone.ReasonShortDesc = "' . $filter . '"';
        else if($filter == 'no-billing')
          $filterQuery = ' AND reasone.ReasonShortDesc != "Billing"';
        else
          $filterQuery = ' ';


      $result =  DB::select('SELECT ticket.TicketNumber,
                                    ticket.Status,
                                    concat(customers.first_name, " " ,customers.last_name) AS name,
                                    
                                    
                                  
                                    reasone.ReasonShortDesc,
                                    ticket.Comment,
                                    admin.Nickname as Assigned,
                                    ticket.LastUpdate,
                                    customers.id,
                                    ticket.TID,
                                    ticket.DateCreated,
                                    building.ShortName
                                      FROM supportTickets ticket
                                        INNER JOIN customers customers
                                          ON ticket.CID = customers.id
                                        INNER JOIN supportTicketReasons reasone
                                          ON ticket.RID = reasone.RID
                                        INNER JOIN serviceLocation building
                                          ON building.LocID = customers.id
                                        INNER JOIN AdminAccess admin
                                          ON ticket.StaffID = admin.ID
                                            WHERE ticket.Status != "closed"
                                            ' . $filterQuery . '
                                              GROUP BY ticket.TicketNumber
                                              ORDER BY ticket.LastUpdate DESC');


                                                    $result =  DB::select('SELECT ticket.TicketNumber,
                                    ticket.Status,
                                    concat(customers.Firstname, " " ,customers.Lastname) AS name,
                                    customers.Address,
                                    customers.Tel,
                                    customers.Email,
                                    reasone.ReasonShortDesc,
                                    ticket.Comment,
                                    admin.Nickname as Assigned,
                                    ticket.LastUpdate,
                                    customers.CID,
                                    ticket.TID,
                                    ticket.DateCreated,
                                    building.ShortName
                                      FROM supportTickets ticket
                                        INNER JOIN customers customers
                                          ON ticket.CID = customers.CID
                                        INNER JOIN supportTicketReasons reasone
                                          ON ticket.RID = reasone.RID
                                        INNER JOIN serviceLocation building
                                          ON building.LocID = customers.LocID
                                        INNER JOIN AdminAccess admin
                                          ON ticket.StaffID = admin.ID
                                            WHERE ticket.Status != "closed"
                                            ' . $filterQuery . '
                                              GROUP BY ticket.TicketNumber
                                              ORDER BY ticket.LastUpdate DESC');

      $resultData =  json_decode(json_encode($result), true);

      foreach($resultData as $x => $data)
      {
        $resultData[$x]['History'] = json_decode(json_encode(DB::select('SELECT * FROM supportTicketHistory sth 
                                                                          INNER JOIN AdminAccess aa 
                                                                            ON sth.StaffID = aa.ID 
                                                                              WHERE sth.TID = ' . $data['TID'] . ' ORDER BY sth.THID DESC'), true));

        $resultData[$x]['Reasons'] = json_decode(json_encode(Ticketreasons::all()), true);
        $resultData[$x]['Admin'] = json_decode(json_encode(Adminaccess::all()), true);

        if(!$resultData[$x]['History'])
          $resultData[$x]['History'] = false;

      }

      return $resultData;
    }
  public function updateTicketHistory(Request $request)
  {

    $data = $request->all();

//    $ticketNoteRecord = new TicketNote();
//    $ticketNoteRecord->id_tickets = $request->id;
//    $ticketNoteRecord->comment = $request->comment;
//    $ticketNoteRecord->id_users = Auth::user()->id;
//    $ticketNoteRecord->save();

    $ticketHistoryRecord = new TicketHistory();
    $ticketHistoryRecord->id_tickets = $request->id;
    $ticketHistoryRecord->id_reasons = $request->id_reasons;
    $ticketHistoryRecord->comment = $request->comment;
    $ticketHistoryRecord->status = $request->status;
    $ticketHistoryRecord->id_users = Auth::user()->id;
    $ticketHistoryRecord->id_users_assigned = $request->id_users_assigned;
    $ticketHistoryRecord->save();


    $updateTicket = Ticket::find($request->id);
    $updateTicket->updated_at = $ticketHistoryRecord->created_at;
    $updateTicket->save();




    $request['ticketId'] = $request->id;
    return $this->getTicketInfo($request);

  }
  public function updateTicketData(Request $request)
  {




    $data = $request->all();
    $request['ticketId'] = $request->id;



    unset($data['id']);

    Ticket::where('id', $request->id)->update($data);
    $data['ticketId'] = $request->id;
    return $this->getTicketInfo($request);




    $idsChart = ['supportTickets' => 'TID', 'supportTicketHistory' => 'TID', 'supportTicketsID' => 'CID'];

    if($request->ajax())
    {
      $table = explode('ID',$request['table'])[0];

      if(!Schema::hasTable($table))
        return "ERROR";

      $data = $request->all();



      unset($data['_token'], $data['id'],$data['table'],$data['bloque']);

      if(isset($data['escalated']))
      {
        $data['Status'] = 'escalated';
        unset($data['escalated']);
      }
      elseif(isset($data['closed']))
      {
        $data['Status'] = 'closed';
        unset($data['closed']);
      }



      switch ($request['table'])
      {
        case 'supportTickets':
          DB::table($request['table'])
            ->where($idsChart[$request['table']], $request['id'])
            ->update($data);
          $resultData = json_decode(json_encode(DB::Select('SELECT st.TID AS id, str.ReasonShortDesc AS RID, st.Status AS Status, aa.Name AS StaffID 
                                                              FROM supportTickets st
                                                                INNER JOIN AdminAccess aa
                                                                  ON st.StaffID = aa.ID
                                                                INNER JOIN supportTicketReasons str
                                                                  ON st.RID = str.RID
                                                                  WHERE TID = ' .$request['id'])), true);
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
        case 'supportTicketsID':
          DB::table($table)
            ->where($idsChart[$table], $request['id'])
            ->update($data);
          $resultData = 'OK';
          break;
      }

      return $resultData;

    }
    return "ERROR:";






  }
}