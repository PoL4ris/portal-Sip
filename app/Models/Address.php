<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
  protected $table = 'address';
    public function customer() {
        return $this->hasOne('App\Models\Customer', 'id');
    }
    public function building() {
        return $this->hasOne('App\Models\Building\Building', 'id_buildings');
    }

    public function ticket() {
      return $this->belongsTo('App\Models\Ticket', 'id_customers', 'id_customers', 'App\Models\Customer');
    }

    public function buildings() {

      return $this->belongsTo('App\Models\Building\Building', 'id_buildings');
    }
    public function customers() {
      return $this->belongsTo('App\Models\Customer', 'id_customers');
    }
    public function customerWhere($id){
      $where = $this->customers;
      return $where->where('id', $id);
    }
    public function contacts() {
      return $this->hasMany('App\Models\Contact', 'id_customers');
    }
}
