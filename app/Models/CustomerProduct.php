<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model {

    public function address()
    {
        return $this->belongsTo('App\Models\Address', 'id_address');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'id_customers');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'id_products');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'id_status');
    }

    public function lastActiveCharge()
    {
        return $this->activeCharges()->orderBy('due_date', 'desc')->take(1);

//        hasOne('App\Models\Charge', 'id_customer_products')
//            ->where(function ($query)
//            {
//                $query->where('charges.status', config('const.charge_status.pending'))
//                    ->orWhere('charges.status', config('const.charge_status.invoiced'));
//            });
    }

    public function activeCharges()
    {
        return $this->hasMany('App\Models\Charge', 'id_customer_products', 'id')
            ->where(function ($query)
            {
                $query->where('charges.status', config('const.charge_status.pending'))
                    ->orWhere('charges.status', config('const.charge_status.invoiced'));
            });
    }

    //    public function port()
//    {
//        return $this->hasOne('App\Models\Port', 'id_customer_products', 'id');
//    }

}
