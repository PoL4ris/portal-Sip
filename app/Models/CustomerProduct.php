<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model {

    public function address()
    {
        return $this->hasOne('App\Models\Address', 'id', 'id_address');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function port()
    {
        return $this->hasOne('App\Models\Port', 'id_customer_products', 'id');
    }

    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'id_products');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status', 'id', 'id_status');
    }


//    public function building()
//    {
////        return $this->hasManyThrough('App\Models\Building', 'App\Models\Address',
////            'id_customers', ''
//////            'App\Post', 'App\User',
////            'country_id', 'user_id', 'id'
////        );
//        return $this->hasOne('App\Models\Product', 'id', 'id_products');
//    }









}
