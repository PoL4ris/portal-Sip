<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

    use Authenticatable,
    Authorizable,
    CanResetPassword;

    protected $table = 'users';
    protected $fillable = ['id', 'first_name', 'last_name', 'email', 'password', 'remember_token', 'social_token', 'social_access', 'avatar', 'alias', 'id_status', 'id_profiles'];
    protected $hidden = ['password', 'remember_token'];

//    public function isAnAdmin() {
//        return ($this->role == 'admin') ? true : false;
//    }

    public function isActive() {
        return ($this->id_status == config('const.status.active')) ? true : false;
    }
    public function status() {
        return $this->hasOne('App\Models\Status', 'id_status');
    }
    public function profile() {
        return $this->hasOne('App\Models\Profile', 'id', 'id_profiles');
    }
    public function accessApps() {
      return $this->hasMany('App\Models\AccessApp', 'id_profiles', 'id_profiles')->where('id_apps','!=', 0)->orderBy('id_apps', 'asc');
    }

}
