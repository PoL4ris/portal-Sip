<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessApp extends Model
{
  public function apps() {
    return $this->belongsTo('App\Models\App', 'id_apps', 'id');
  }
}
