<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * 
     * @return type
     */
    public function type() {

        return $this->hasOne('App\Models\Type', 'id_types');
    }
}
