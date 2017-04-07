<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPort extends Model
{
    protected $table = 'customer_port';

    protected $fillable = ['customer_id', 'port_id'];

}