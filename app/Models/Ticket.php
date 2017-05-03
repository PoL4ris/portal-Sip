<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;


class Ticket extends Model
{
  public function __construct()
  {
    DB::connection()->enableQueryLog();
  }
  public function customer() {
    return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
  }
  public function reason() {
    return $this->hasOne('App\Models\Reason', 'id', 'id_reasons');
  }
  public function ticketNote() {
    return $this->hasOne('App\Models\TicketNote', 'id', 'id_ticket_notes');
  }
  public function lastTicketHistory() {
    return $this->hasOne('App\Models\TicketHistory', 'id_tickets')->orderBy('id','desc');
  }
  public function user() {
    return $this->hasOne('App\Models\User', 'id', 'id_users');
  }
  public function userAssigned() {
    return $this->hasOne('App\Models\User', 'id','id_users_assigned');
  }
  public function address() {
    return $this->belongsTo('App\Models\Address', 'id_customers', 'id_customers', 'App\Models\Customer');
  }
  public function contacts(){
    return $this->belongsTo('App\Models\Contact', 'id_customers', 'id_customers', 'App\Models\Customer');
  }
  public function ticketHistoryFull() {
    return $this->hasMany('App\Models\TicketHistory', 'id_tickets', 'id')->orderBy('id','desc');
  }
  public function historyReason() {

    return $this->belongsToMany('App\Models\Reason', 'reasons', 'id_reasons', 'id');
  }
  public function building()
  {
    return $this->belongsTo('App\Models\Building', 'id_address', 'id_address', 'App\Models\Address');
  }




  public function buildings() {
    return $this->belongsToMany('App\Models\Building');
  }

  public function difftime(){
    return;
  }

}
