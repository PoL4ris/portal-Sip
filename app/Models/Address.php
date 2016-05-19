<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
  protected $table = 'address';
    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id');
    }

    /**
     * 
     * @return type
     */
    public function building() {

        return $this->hasOne('App\Models\Building\Building', 'id_buildings');
    }
}
