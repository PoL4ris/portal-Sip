<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * 
     * @return type
     */
    public function tickets() {

        return $this->hasMany('App\Models\Ticket', 'id_customers');
    }

    public function reason3Tickets() {

        return $this->hasMany('App\Models\Ticket', 'id_customers')
            ->where('id_reasons', 2);
    }
    
    /**
     * 
     * @return type
     */
    public function type() {

        return $this->hasOne('App\Models\Type', 'id', 'id_types');
    }

    /**
     * 
     * @return type
     */
    public function status() {

        return $this->hasOne('App\Models\Status', 'id_status');
    }
  
}
