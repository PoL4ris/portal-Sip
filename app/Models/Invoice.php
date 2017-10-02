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
            $detailsArray [] = array_merge(json_decode($charge->details, true), ['start_date' => $charge->start_date, 'end_date' => $charge->end_date]);
        }

        return $detailsArray;
    }

    public function address()
    {
        return $this->hasOne('App\Models\Address', 'id', 'id_address');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }
    public function types()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\InvoiceLog', 'id_invoices');
    }
}
