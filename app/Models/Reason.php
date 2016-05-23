<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
  protected $table = 'reasons';
    /**
     * 
     * @return type
     */
    public function category() {
        return $this->hasOne('App\Models\Category', 'id_categories');
    }
}
