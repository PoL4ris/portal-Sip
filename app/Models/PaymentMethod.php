<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{

    public function type() {
        return $this->hasOne('App\Models\Type', 'id_types');
    }

    public function address() {
        return $this->hasOne('App\Models\Address', 'id', 'id_address');
    }

    public function customer() {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function getProperties(){
        $properties = json_decode($this->properties, true);
        return $properties;
//        return array_shift($properties);
    }

    public function getProperty($property){
        $properties = $this->getProperties();
        return isset($properties[$property]) ? $properties[$property] : null;
    }
}
