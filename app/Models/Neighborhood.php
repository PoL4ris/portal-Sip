<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    protected $fillable = ['id', 'name'];

    public function ttest() {
        return $this->belongsTo('App\Models\Address', 'id_', 'id', 'App\Models\CustomerProduct');
    }
}
