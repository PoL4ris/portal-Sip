<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{

    protected $fillable = ['id_customers', 'comment', 'created_by'];

    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id_customers');
    }
}
