<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Address;


class Ticket extends Model
{

    public function __construct()
    {
        DB::connection()->enableQueryLog();

    }
    //  public function getTicketData()
    //  {
    //    $this->hasOne('App\Models\Customer', 'id_customers')
    //  }
    /**
   *
   * @return type
   */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    /**
   *
   * @return type
   */
    public function reason() {

        return $this->hasOne('App\Models\Reason', 'id', 'id_reasons');
    }

    /**
   *
   * @return type
   */
    public function ticketNote() {

        return $this->hasOne('App\Models\TicketNote', 'id', 'id_ticket_notes');
    }

  /**
   *
   * @return type
   */
    public function ticketHistory() {
        return $this->hasOne('App\Models\TicketHistory', 'id_tickets');
    }

    /**
   *
   * @return type
   */
    public function user() {

        return $this->hasOne('App\Models\User', 'id', 'id_users');
    }


    /**
   *
   * @return type
   */
    public function userAssigned() {

        return $this->hasOne('App\Models\User', 'id','id_users_assigned');

    }

    /**
   *
   * @return type
   */
    public function address() {
        return $this->belongsTo('App\Models\Address', 'id_customers', 'id_customers', 'App\Models\Customer');
    }

    public function contacts(){
      return $this->belongsTo('App\Models\Contact', 'id_customers', 'id_customers', 'App\Models\Customer');
    }
}
