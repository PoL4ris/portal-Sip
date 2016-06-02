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

        return $this->hasOne('App\Models\Product', 'id', 'id_products');
    }
    public function status() {
        return $this->hasOne('App\Models\Status', 'id', 'id_status');
    }
    public function port() {
        return $this->hasOne('App\Models\Port', 'id', 'id_customer_products');
    }
}
