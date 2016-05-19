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

    /**
   * The database table used by the model.
   *
   * @var string
   */
    protected $table = 'users';

    /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
    protected $fillable = ['name', 'email', 'password', 'access', 'role', 'avatar'];

    /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
    protected $hidden = ['password', 'remember_token'];

    public function isAnAdmin() {
        return ($this->role == 'admin') ? true : false;
    }

    public function isActive() {
        return ($this->access == 'yes') ? true : false;
    }

    /**
     * 
     * @return type
     */
    public function status() {

        return $this->hasOne('App\Models\Status', 'id_status');
    }    

    /**
     * 
     * @return type
     */
    public function profile() {

        return $this->hasOne('App\Models\Profile', 'id_profiles');
    }

}