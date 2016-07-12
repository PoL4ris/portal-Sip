<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{

  public function type() {
    return $this->hasOne('App\Models\Type', 'id_types');
  }
  public function address() {
    return $this->hasOne('App\Models\Address', 'id_address');
  }
  public function customer() {
    return $this->hasOne('App\Models\Customer', 'id_customers');
  }
}
