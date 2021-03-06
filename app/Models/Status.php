<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';

    protected $fillable = ['id', 'name'];

    public function type() {
        return $this->belongsTo('App\Models\Customer', 'id_types');
    }
}
