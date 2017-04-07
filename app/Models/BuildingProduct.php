<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingProduct extends Model
{
    public function product() {
        return $this->hasOne('App\Models\Product', 'id', 'id_products');
    }
}
