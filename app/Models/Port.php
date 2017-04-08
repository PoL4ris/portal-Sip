<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    public function networkNodes() {
        return $this->belongsTo('App\Models\NetworkNode', 'id_network_nodes');
    }

    public function customer() {
        return $this->hasOne('App\Models\Customer', 'id_customers');
    }
    
    public function customers() {
        return $this->belongsToMany('App\Models\Customer');
    }
    
    
    public function customerProduct() {
        return $this->hasOne('App\Models\CustomerProduct', 'id_customer_products');
    }
}
