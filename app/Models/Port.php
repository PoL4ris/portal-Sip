<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
  public function networkNodes() {
    return $this->belongsTo('App\Models\NetworkNode', 'id');
  }
}
