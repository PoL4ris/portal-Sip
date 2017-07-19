<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

    protected $fillable = ['id_customers', 'id_address', 'status'];

    public function charges()
    {
        return $this->hasMany('App\Models\Charge', 'id_invoices');
    }

    public function details()
    {

        $charges = $this->charges;
        $detailsArray = array();
        foreach ($charges as $charge)
        {
            if ($charge->details == '')
            {
                continue;
            }
            $chargeDetailsArray = json_decode($charge->details, true);
            $detailsArray[] = $chargeDetailsArray;
        }

        return $detailsArray;
    }

    public function address()
    {
        return $this->hasOne('App\Models\Address', 'id', 'id_address');
    }
}
