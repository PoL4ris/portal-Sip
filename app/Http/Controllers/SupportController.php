<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Schema;
use App\Models\Support\Tickethistory;
use App\Models\Support\Ticketreasons;
use App\Models\Support\Adminaccess;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Address;


class SupportController extends Controller
{
  public function __construct() {
//    $this->middleware('auth');
    DB::connection()->enableQueryLog();

  }
  public function dashboardTemp()
  {


//     return  Ticket::with('customer', 'reason', 'ticketNote', 'user', 'userAssigned')->take(20)->skip(0)->first();
//     $war =  Ticket::with('customer', 'reason', 'ticketNote', 'user', 'userAssigned','addressRelation')





     $war =  Ticket::with('customer')

     ->take(20)->skip(0)->first()->toArray();






    dd($war);



    return Customer::with(['tickets' => function ($query) {
      $query->where('id_reasons', 2);
    }, 'type'])->find(1782);












    $queries = DB::getQueryLog();
    $last_query = end($queries);
    dd($last_query);

//    return Ticket::with('customer', 'reason', 'ticketNote', 'user', 'userAssigned');

//    return Customer::with(['tickets' => function ($query) {
//      $query->where('id_reasons', 2);
//    }, 'type'])->find(1782);
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

  public function updateTicketData(Request $request)
  {
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




