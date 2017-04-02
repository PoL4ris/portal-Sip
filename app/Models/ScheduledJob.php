<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledJob extends Model
{
    public function getProperty($key){
        $properties = json_decode($this->properties, true);
        return isset($properties[$key]) ? $properties[$key] : null;
    }

    public function setProperty($key, $value){
        $properties = json_decode($this->properties, true);
        $properties[$key] = $value;
        $this->properties = json_encode($properties);
    }
}
