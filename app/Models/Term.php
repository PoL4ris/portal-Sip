<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    /**
     * 
     * @return type
     */
    public function product() {

        return $this->hasOne('App\Models\Product', 'id_products');
    }
}
