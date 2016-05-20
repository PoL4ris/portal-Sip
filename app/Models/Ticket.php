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

  public function ticketHistory() {
    return $this->hasOne('App\Models\TicketHistory', 'id_tickets');
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
    return $this->hasMany('App\Models\TicketHistory', 'id_tickets', 'id');
  }
  public function historyReason() {
//    return $this->belongsToMany('App\Models\Reason','reasons' ,'qqq', 'rrr', 'App\Models\TicketHistory')
//      ->withPivot('xxx','yyy', 'zzz');


    return $this->belongsToMany('App\Models\Reason', 'reasons', 'id_reasons', 'id');
  }
}
