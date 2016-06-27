<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{

  public function customer() {
    return $this->hasMany('App\Models\Customer', 'id_buildings', 'id');
  }

  public function address() {
    return $this->hasMany('App\Models\Address', 'id_buildings', 'id')
                  ->whereNull('id_customers');
  }
  public function products() {
    return $this->hasMany('App\Models\BuildingProduct', 'id_buildings');
  }

}
