<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingPropertyValue extends Model
{
    protected $fillable = ['id', 'id_buildings', 'id_building_properties', 'value'];
}
