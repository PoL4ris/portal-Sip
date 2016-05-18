<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * 
     * @return type
     */
    public function users() {

        return $this->hasMany('App\Models\User', 'id');
    }
}
