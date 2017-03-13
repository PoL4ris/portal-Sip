<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
  public function user() {
    return $this->hasOne('App\Models\User', 'id', 'id_users');
  }
}
