<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Log;
use Schema;
use Auth;
use SendMail;
//Models
use App\Models\TicketNote;
use App\Models\Support\Ticketreasons;
use App\Models\Support\Adminaccess;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Address;
use App\Models\Product;
use App\Models\Building;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        DB::connection()->enableQueryLog();

    }

    /**
     * @param Request $request
     * id ticket, id customer to find and update.
     * @return ticket data.
     */
    public function updateCustomerOnTicket(Request $request)
    {
        $ticket = Ticket::find($request->id_ticket);
        $ticket->id_customers = $request->id_customers;
        $ticket->save();

        $request['ticketId'] = $request->id_ticket;

        return $this->getTicketInfo($request);
    }

    /**
     * @param Request $request
     * id = ticket id to find.
     * @return requested ticket info.
     */
    public function getTicketInfo(Request $request)
    {
        return Ticket::with('customer',
                            'address',
                            'contacts',
                            'userAssigned',
                            'reason',
                            'ticketNote',
                            'ticketHistoryFull')
                     ->find($request['ticketId']);
    }

    /**
     * @return list of open tickets.
     */
    public function getAllOpenTickets()
    {
        $records = Ticket::with('customer',
                                'reason',
                                'ticketNote',
                                'lastTicketHistory',
                                'user',
                                'userAssigned',
                                'address',
                                'contacts')
                         ->where('status', '!=', config('const.ticket_status.closed'))
                         ->get();

        $result = $this->getOldTimeTicket($records);

        return $result;
    }

    /**
     * @param $record
     * collection of tickets.
     * @return tickets with 12/24/48 label added to display properly
     */
    public function getOldTimeTicket($record)
    {
        //$new_time   = date("Y-m-d H:i:s");
        $time12 = date("Y-m-d H:i:s", strtotime('-12 hours'));
        $time24 = date("Y-m-d H:i:s", strtotime('-24 hours'));
        $time48 = date("Y-m-d H:i:s", strtotime('-48 hours'));

        foreach ($record as $k => $rec)
        {
            if ($rec['updated_at'] <= $time48)
                $record[$k]['old'] = 'old-red';
            else if ($rec['updated_at'] <= $time24)
                $record[$k]['old'] = 'old-yellow';
            else if ($rec['updated_at'] <= $time12)
                $record[$k]['old'] = 'old-green';
            else
            {
                $record[$k]['old'] = 'old';
                $datetime1 = date_create($rec->toArray()['created_at']);
                $datetime2 = date_create(date("Y-m-d H:i:s"));
                $interval  = date_diff($datetime1, $datetime2);
                $formated  = $interval->format('%H');

                if($formated > 0)
                    $record[$k]['old_by'] = $formated . ' hours ago.';
            }

            if ($record[$k]['updated_at'] == null)
            {
                $record[$k]['updated_at'] = $record[$k]['created_at'];
            }
        }

        return $record;
    }

    /**
     * @return list of non billing tickets.
     */
    public function getNoneBillingTickets()
    {
        $tickets = Ticket::with('customer',
                               'reason',
                               'ticketNote',
                               'lastTicketHistory',
                               'user',
                               'userAssigned',
                               'address',
                               'contacts')
                        ->where('id_reasons', '!=', config('const.reason.billing'))
                        ->where('id_reasons', '!=', config('const.reason.internal_billing'))
                        ->where('status', '!=', config('const.ticket_status.closed'))
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $tickets = $this->getOldTimeTicket($tickets);

        return $tickets;
    }

    /**
     * @return List of billing open tickets.
     */
    public function getBillingTickets()
    {
        $record = Ticket::with('customer',
                               'reason',
                               'ticketNote',
                               'lastTicketHistory',
                               'user',
                               'userAssigned',
                               'address',
                               'contacts')
                        ->where('status', '!=', config('const.ticket_status.closed'))
                        ->where(function ($query) {
                                    $query->where('id_reasons',   config('const.reason.billing'))
                                          ->orWhere('id_reasons', config('const.reason.internal_billing'));
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $result = $this->getOldTimeTicket($record);

        return $result;
    }

    /**
     * @return list of tickets that belongs to logged user.
     */
    public function getMyTickets()
    {
        $record = Ticket::with('customer',
                               'reason',
                               'ticketNote',
                               'lastTicketHistory',
                               'user',
                               'userAssigned',
                               'address',
                               'contacts')
                         ->where('id_users_assigned', Auth::user()->id)
                         ->where('status', '!=', config('const.ticket_status.closed'))
                         ->orderBy('updated_at', 'desc')
                         ->get();

        $result = $this->getOldTimeTicket($record);

        return $result;
    }

    /**
     * @param Request $request
     * id = id ticket to get history about
     * @return requested record.
     */
    public function supportTicketHistory(Request $request)
    {
        return TicketHistory::with('reason', 'user')->find($request->id);
    }

    /**
     * @return List of tickets with 12/24/48 filter.
     */
    public function getTicketOpenTime()
    {
        $time12 = date("Y-m-d H:i:s", strtotime('-12 hours'));
        $time24 = date("Y-m-d H:i:s", strtotime('-24 hours'));
        $time48 = date("Y-m-d H:i:s", strtotime('-48 hours'));

        $old48  = Ticket::where('updated_at', '<=', $time48)
                        ->where('status', '!=', config('const.ticket_status.closed'))
                        ->count();

        $old24  = Ticket::where('updated_at', '<=', $time24)
                        ->where('updated_at', '>=', $time48)
                        ->where('status', '!=', config('const.ticket_status.closed'))
                        ->count();

        $old12  = Ticket::where('updated_at', '<=', $time12)
                        ->where('updated_at', '>=', $time24)
                        ->where('status', '!=', config('const.ticket_status.closed'))
                        ->count();

        $old    = Ticket::where('status', '!=', config('const.ticket_status.closed'))->count();

        $result['old48'] = $old48;
        $result['old24'] = $old24;
        $result['old12'] = $old12;
        $result['old']   = $old;

        return $result;
    }

    /**
     * @param Request $request
     * id = building ID to get active products to display
     * @return requested id building with active products.
     */
    public function getAvailableServices(Request $request)
    {
        $building = Building::find($request->id);
        $activeServices = $building->activeParentProducts();
        $activeServices->transform(function ($service, $key) {
            if($service->product->frequency == 'onetime'){
                $service->product->frequency = 'one time';
            }
            return $service;
        });

        return $activeServices;
    }

    /**
     * @return Products List.
     */
    public function getProducts()
    {
        return Product::with('type', 'propertyValues')->get();
    }

    /**
     * @param Request $request
     * id = ticket id to find , update and to add a new ticket history.
     * DB fields for ticketHistory table.
     * @return ticket history data with ticket history records.
     */
    public function updateTicketHistory(Request $request)
    {
        $ticketHistoryRecord = new TicketHistory();
        $ticketHistoryRecord->id_tickets = $request->id;
        $ticketHistoryRecord->id_reasons = $request->id_reasons;
        $ticketHistoryRecord->comment    = $request->comment;
        $ticketHistoryRecord->status     = $request->status;
        $ticketHistoryRecord->id_users   = Auth::user()->id;
        $ticketHistoryRecord->id_users_assigned = $request->id_users_assigned;
        $ticketHistoryRecord->save();

        $updateTicket = Ticket::find($request->id);
        $updateTicket->id_users   = Auth::user()->id;
        $updateTicket->status     = $request->status;
        $updateTicket->updated_at = $ticketHistoryRecord->created_at;
        $updateTicket->save();

        //1 = new ticket
        //2 = update ticket
        $response = SendMail::ticketMail($ticketHistoryRecord, $request->status);
        Log::info($response);

        $request['ticketId'] = $request->id;

        return $this->getTicketInfo($request);
    }

    /**
     * @param Request $request
     * id = ticket id to find and update.
     * id_reasons = reason updated for the ticket.
     * id_users_assigned" = user assigned to the ticket
     * status = new status of the ticket.
     * @return updated ticket info.
     */
    public function updateTicketData(Request $request)
    {
        $request['ticketId'] = $request->id;
        $ticket = Ticket::find($request->id);
        $ticket->id_reasons  = $request->id_reasons;
        $ticket->status      = $request->status;
        $ticket->id_users_assigned = $request->id_users_assigned;
        $ticket->save();

        return $this->getTicketInfo($request);
    }

    /**
     * @param Request $request
     * querySearch = sting to search and find on ticket requirements.
     * @return list of tickets matching all cases.
     */
    public function getTicketsSearch(Request $request)
    {
        $query = Ticket::join('customers', 'customers.id', '=', 'tickets.id_customers')
                       ->select('*', 'customers.id as idCustomer', 'tickets.id as idTicket');

        $string = $request->querySearch;
        $limit = 20;

        $patternUno = '/^([Ss][tT]\-[0-9]+).*/';
        $patternDos = '/^([0-9]+).*/';

        preg_match($patternUno, $string, $stType);
        preg_match($patternDos, $string, $noType);

        if ($stType || $noType)
            return $query->where('tickets.ticket_number', 'like', '%' . $request->querySearch . '%')
                         ->take($limit)
                         ->get()
                         ->load('address');

        $stringArray = explode(' ', $string);

        foreach ($stringArray as $word)
            $resultFilter = $query->where('customers.first_name', 'like', '%' . $word . '%')
                                  ->orWhere('customers.last_name', 'like', '%' . $word . '%');

        $result = $resultFilter->take($limit)->get();

        if (count($result) === 0)
            return $query->orWhere('tickets.comment', 'like', '%' . $request->querySearch . '%')
                         ->take($limit)
                         ->get()
                         ->load('address');
        else
            return $result->load('address');
    }


    public function dashboard(Request $request)//TMP DISABLED VERIFY.
    {
        dd();
        if ($request->filter)
            $ticketsData = $this->getTicketsData($request->filter);
        else
            $ticketsData = $this->getTicketsData('');

        return $ticketsData;
        //    return view('support.dashboard', ['tickets' => $ticketsData]);
    }
    public function getTicketCustomerList(Request $request)//Verify Usage
    {
        if ($request->unit == 'false')
            return Address::where('code', 'LIKE', '%' . $request->code . '%')->get();
        else if ($request->code == 'false')
            return Address::where('unit', 'LIKE', '%' . $request->unit . '%')->get();
        else
            return Address::where('unit', 'LIKE', '%' . $request->unit . '%')->where('code', 'LIKE', '%' . $request->code . '%')->get();
    }
}
