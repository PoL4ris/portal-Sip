<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingTransactionLog extends Model {

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function building()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }
}
