<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model
{
    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id_customers');
    }

    /**
     * 
     * @return type
     */
    public function product() {

        return $this->hasOne('App\Models\Product', 'id_products');
    }

    /**
     * 
     * @return type
     */
    public function status() {

        return $this->hasOne('App\Models\Status', 'id_status');
    }
}
