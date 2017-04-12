<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkNode extends Model {
    
    public function masterRouter() {
        return $this->hasOne('App\Models\NetworkNode', 'id_address', 'id_address')
            ->where('id_types', config('const.type.router'))
            ->where('role', 'Master');
    }
    
    public function getProperty($key){
        $properties = json_decode($this->properties, true);
        if($this->id_types == config('const.type.switch') && count($properties) > 0){
            return isset($properties[0][$key]) ? $properties[0][$key] : null;    
        }
        return isset($properties[$key]) ? $properties[$key] : null;
    }

    public function getProperties(){
        return json_decode($this->properties, true);
    }

    public function setProperty($key, $value){
        $properties = json_decode($this->properties, true);
        if($properties != null && $properties != false) {
            $properties[$key] = $value;
        } else {
            $properties = [$key => $value];
        }

        $this->properties = json_encode($properties);
    }
}
