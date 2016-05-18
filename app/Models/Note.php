<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id_customers');
    }
}
