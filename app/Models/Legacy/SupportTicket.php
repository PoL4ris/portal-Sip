<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use DB;

class SupportTicket extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'old-portal';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supportTickets';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'TID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     *
     * @return type
     */
    public function reason() {
        return $this->hasOne('App\Models\Legacy\SupportTicketReason', 'RID', 'RID');
    }

    /**
     *
     * @return type
     */
    public function customer() {
        return $this->hasOne('App\Models\Legacy\Customer', 'CID', 'CID');
    }

    /**
     *
     * @return type
     */
    public function history() {
        return $this->hasMany('App\Models\Legacy\SupportTicketHistory', 'TID', 'TID');
    }

    /**
     *
     * @return type
     */
    public function firstHistory() {
        return $this->hasMany('App\Models\Legacy\SupportTicketHistory', 'TID', 'TID')
            ->first();
    }

    /**
     *
     * @return type
     */
    public function firstHistoryAfter() {
        return $this->hasMany('App\Models\Legacy\SupportTicketHistory', 'TID', 'TID')
            ->first();
    }

    public function getNewTicketsWithFirstHistoryByLocID($locID, $startDate, $endDate) {

        $tickets = SupportTicket::join('supportTicketHistory', 'supportTicketHistory.TID', '=', 'supportTickets.TID')
            ->join('customers', 'customers.CID', '=', 'supportTickets.CID')
            ->where('customers.LocID', '=', $locID)
            ->whereBetween('supportTickets.DateCreated', array($startDate, $endDate))
            ->groupBy('supportTickets.TID')
            ->select('supportTickets.*', 'supportTicketHistory.THID',
                     'supportTicketHistory.Comment as HistComment', 'supportTicketHistory.RID as HistRID',
                     'supportTicketHistory.Status as HistStatus','supportTicketHistory.Timestamp')
            ->get();

        return $tickets;
    }

    /**
     * Returns tickets that have not been responded to for 24 hours or longer
     * at the specified date
     * @param  integer $locID             service location ID (aka building ID)
     * @param  Carbon Carbon $carbonDate date and time at which the check is made
     * @return Collection tickets that have not been responded to for 24 hours or longer
     */
    public function getOutstandingTicketsByLocID($locID, Carbon $carbonDate) {

        // Get the timestamp 24 hours prior to $carbonDate so we can check
        // the ticket creation time against it
        $ticketCreateDate = $carbonDate->copy()->subDay()->format('Y-m-d 00:00:00');

        // Use the $carbonDate to check for the first ticket response
        $timestamp = $carbonDate->format('Y-m-d 00:00:00');

        $tickets = SupportTicket::join('supportTicketHistory', 'supportTicketHistory.TID', '=', 'supportTickets.TID')
            ->join('customers', 'customers.CID', '=', 'supportTickets.CID')
            ->where('customers.LocID', '=', $locID)
            ->where('supportTickets.DateCreated', '<=', $ticketCreateDate)
            ->where('supportTicketHistory.Timestamp', '>=', $timestamp)
            ->where(DB::raw('TIMESTAMPDIFF(DAY, supportTickets.DateCreated,supportTicketHistory.Timestamp) >= 1'))
            ->groupBy('supportTickets.TID')
            ->select('supportTickets.*', 'supportTicketHistory.THID',
                     'supportTicketHistory.Comment as HistComment', 'supportTicketHistory.RID as HistRID',
                     'supportTicketHistory.Status as HistStatus','supportTicketHistory.Timestamp')
            ->get();

        return $tickets;
    }

    /**
     * Returns non-internal tickets (RID is not SYS) that were not responded to after 24 hours within the specified month
     * @param  integer $locID            service location ID (aka building ID)
     * @param  integer $month            month during which to check for outstanding tickets
     * @param  integer $year             year corresponding to the desired month
     * @return Collection tickets that have not been responded to for 24 hours or longer
     */
    public function getOutstandingTicketsOfMonthByLocID($locID, $month, $year) {

        $carbonDate = Carbon::createFromFormat('d/m/Y H:i:s', '01/'.$month.'/'.$year.' 00:00:00');
        $minTicketCreateDate = $carbonDate->format('Y-m-d 00:00:00');
        $maxTicketCreateDate = $carbonDate->copy()->addMonth()->subDays(2)->format('Y-m-d 00:00:00');

        $sub = SupportTicket::join('supportTicketHistory', 'supportTicketHistory.TID', '=', 'supportTickets.TID')
            ->join('customers', 'customers.CID', '=', 'supportTickets.CID')
            ->join('supportTicketReasons', 'supportTickets.RID', '=', 'supportTicketReasons.RID')
            ->where('customers.LocID', '=', $locID)
            ->where('supportTicketReasons.ReasonCategory', '!=', 'SYS')
            ->whereRaw(" TIMESTAMP(`supportTickets`.`DateCreated`) <= '".$maxTicketCreateDate."'")
            ->groupBy('supportTickets.TID')
            ->orderBy('supportTicketHistory.Timestamp', 'ASC')
            ->select('supportTickets.*', 'supportTicketHistory.THID',
                     'supportTicketReasons.ReasonCategory',
                     'supportTicketHistory.Comment as HistComment', 'supportTicketHistory.RID as HistRID',
                     'supportTicketHistory.Status as HistStatus','supportTicketHistory.Timestamp as FirstResponse')
            ->toSql();

        $tickets = SupportTicket::select(DB::raw('TID, THID, Comment, DateCreated, FirstResponse, ReasonCategory'))
            ->from (DB::raw("({$sub}) as t"))
            ->whereRaw(" TIMESTAMP(`t`.`FirstResponse`) >= '".$minTicketCreateDate."'")
            ->whereRaw(" TIMESTAMP(`t`.`FirstResponse`) >= TIMESTAMPADD(DAY,1,`t`.`DateCreated`)")
            ->orderBy('FirstResponse','ASC')
            ->setBindings([$locID, 'SYS'])
            ->get();

        return $tickets;
    }

    public function getNewTicketsByLocID($locID, $startDate, $endDate) {

        return SupportTicket::with('history')
            ->join('customers', 'customers.CID', '=', 'supportTickets.CID')
            ->join('supportTicketReasons', 'supportTickets.RID', '=', 'supportTicketReasons.RID')
            ->where('customers.LocID', '=', $locID)
            ->whereRaw(" TIMESTAMP(`supportTickets`.`DateCreated`) >= '".$startDate."'")
            ->whereRaw(" TIMESTAMP(`supportTickets`.`DateCreated`) < '".$endDate."'")
            ->select('supportTickets.*', 'supportTicketReasons.ReasonCategory as ReasonCategory')
            ->orderBy('supportTickets.DateCreated')
            ->get();
    }

    public function getNewNonSysTicketsByLocID($locID, $startDate, $endDate) {

        return SupportTicket::with('history')
            ->join('customers', 'customers.CID', '=', 'supportTickets.CID')
            ->join('supportTicketReasons', 'supportTickets.RID', '=', 'supportTicketReasons.RID')
            ->where('customers.LocID', '=', $locID)
            ->where('supportTicketReasons.ReasonCategory', '!=', 'SYS')
            ->whereRaw(" TIMESTAMP(`supportTickets`.`DateCreated`) >= '".$startDate."'")
            ->whereRaw(" TIMESTAMP(`supportTickets`.`DateCreated`) < '".$endDate."'")
            ->select('supportTickets.*', 'supportTicketReasons.ReasonCategory as ReasonCategory')
            ->orderBy('supportTickets.DateCreated')
            ->get();
    }
}
