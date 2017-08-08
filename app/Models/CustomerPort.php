<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPort extends Model {

    protected $table = 'customer_port';

    protected $fillable = ['customer_id', 'port_id'];

    public function portWithNetworkNode()
    {
        $port = $this->port();
        if($port != null){
            return $port->with('networkNode');
        }
        return null;
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'customer_id');
    }

    public function port()
    {
        return $this->hasOne('App\Models\Port', 'id', 'port_id');
    }

}