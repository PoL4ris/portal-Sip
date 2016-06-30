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
}
