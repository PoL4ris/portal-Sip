<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model {

    protected $fillable = ['id_network_nodes', 'port_number'];

    public function networkNode()
    {
        return $this->belongsTo('App\Models\NetworkNode', 'id_network_nodes');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function customers()
    {
        return $this->belongsToMany('App\Models\Customer');
    }


    public function customerProduct()
    {
        return $this->hasOne('App\Models\CustomerProduct', 'id_customer_products');
    }

    public function address()
    {
        $networkNode =  $this->networkNode;
        if($networkNode != null){
            return $networkNode->address();
        }
        return $this->networkNode();
    }
}
