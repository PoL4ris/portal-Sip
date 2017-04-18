<?php

namespace App\Building;

use Illuminate\Database\Eloquent\Model;

class BuildingProducts extends Model
{
    public function products(){
        return $this->hasOne('App\Models\Product', 'id', 'id_products');
    }
}
