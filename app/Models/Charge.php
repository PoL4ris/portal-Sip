<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = ['id', 'name', 'address', 'description', 'details', 'amount', 'qty', 'id_customers', 'id_products', 'id_address', 'status', 'type', 'comment'];
    protected $guarded = [];
}
