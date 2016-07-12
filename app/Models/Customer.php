<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NetworkNodes;



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
    return $this->hasOne('App\Models\Contact', 'id_customers')->where('id_types', 1);
  }
  public function address() {
    return $this->hasOne('App\Models\Address', 'id_customers');
  }
  public function payment() {
    return $this->hasOne('App\Models\PaymentMethod', 'id_customers');
  }
  public function ticketHistory()
  {
    return $this->hasMany('App\Models\Ticket', 'id_customers', 'id');
  }
  public function services()
  {
    return $this->hasMany('App\Models\CustomerProduct', 'id_customers', 'id');
  }
  public function product()
  {
    return $this->belongsTo('App\Models\Product', 'id_customers', 'id', 'App\Models\CustomerProduct');
  }
  public function building()
  {
    return $this->belongsTo('App\Models\Address', 'id', 'id_buildings', 'App\Models\Building\Building');
  }

  public function getNetworkNodes($id = null)
  {
    if($id == null){
      $id = $this->attributes['id'];
    }
    return NetworkNode::join('ports', 'ports.id_network_nodes', '=', 'network_nodes.id')
      ->join('customers', 'ports.id_customers', '=', 'customers.id')
      ->where('ports.id_customers', '=', $id)
      ->select('*')
      ->get();
  }

}
