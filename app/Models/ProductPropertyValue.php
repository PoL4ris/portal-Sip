<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPropertyValue extends Model
{
    public function properties() {
        return $this->hasOne('App\Models\ProductProperty', 'id', 'id_product_properties');
    }
}
