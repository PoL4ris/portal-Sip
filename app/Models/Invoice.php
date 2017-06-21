<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

    protected $fillable = ['id_customers', 'id_address', 'status'];

    public function charges()
    {
        return $this->hasMany('App\Models\Charge', 'id_invoices');
    }
}
