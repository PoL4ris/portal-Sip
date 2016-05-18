<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * 
     * @return type
     */
    public function type() {

        return $this->hasOne('App\Models\Type', 'id_types');
    }

    /**
     * 
     * @return type
     */
    public function address() {

        return $this->hasOne('App\Models\Address', 'id_address');
    }

    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id_customers');
    }
}
