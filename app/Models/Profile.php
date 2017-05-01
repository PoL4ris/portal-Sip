<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

  public function users() {

      return $this->hasMany('App\Models\User', 'id');
  }
  public function accessApps() {

    return $this->hasMany('App\Models\AccessApp', 'id_profiles');
  }
}
