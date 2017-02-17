<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhcpLease extends Model
{
    public function networkNode() {
        return $this->hasOne('App\Models\NetworkNode', 'mac_address', 'switch');
    }
    
    public function port() {
        return $this->hasManyThrough('App\Models\Port', 'App\Models\NetworkNode', 'mac_address', 'id_network_nodes',  'switch')
            ->where('port_number',$this->attributes['interface']);
    }
    
    public function customer() {
        return $this->hasManyThrough('App\Models\Port', 'App\Models\NetworkNode', 'mac_address', 'id_network_nodes',  'switch')
            ->where('port_number',$this->attributes['interface']);
    }
}
