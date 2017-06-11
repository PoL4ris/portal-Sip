<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public function charges()
    {
        return $this->hasMany('App\Models\Charge', 'id_invoices');
    }
}
