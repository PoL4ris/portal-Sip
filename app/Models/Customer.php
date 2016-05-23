<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
  public function tickets() {
    return $this->hasMany('App\Models\Ticket', 'id_customers');
  }
  public function reason3Tickets() {
    return $this->hasMany('App\Models\Ticket', 'id_customers')
      ->where('id_reasons', 2);
  }
  public function type() {
    return $this->hasOne('App\Models\Type', 'id', 'id_types');
  }
  public function status() {
    return $this->hasOne('App\Models\Status', 'id','id_status');
  }
  public function contacts() {
    return $this->hasOne('App\Models\Contact', 'id_customers');
  }
  public function address() {
    return $this->hasOne('App\Models\Address', 'id_customers');
  }
  public function payment() {
    return $this->hasOne('App\Models\Payment', 'id_customers');
  }
  public function ticketHistory()
  {
    return $this->hasMany('App\Models\Ticket', 'id_customers', 'id');
  }
}
