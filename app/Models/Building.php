<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Building extends Model
{
    public function customers() {
        return $this->hasMany('App\Models\Customer', 'id_buildings', 'id');
    }
    public function address() {
        return $this->hasMany('App\Models\Address', 'id_buildings', 'id')
            ->whereNull('id_customers');
    }
    public function customerAddresses() {
        return $this->hasMany('App\Models\Address', 'id_buildings', 'id')
            ->whereNotNull('id_customers');
    }
    public function neighborhood() {
        return $this->hasOne('App\Models\Neighborhood', 'id', 'id_neighborhoods');
    }
    public function contacts() {
        return $this->hasMany('App\Models\BuildingContact', 'id_buildings', 'id');
    }
    public function buildingProducts() {
        return $this->hasMany('App\Models\BuildingProduct', 'id_buildings', 'id'); //->with('product');
//        return $this->hasOne('App\Models\BuildingProduct', 'id', 'id_buildings');
    }
    public function products() {
      return $this->hasMany('App\Models\BuildingProduct', 'id_buildings', 'id')->with('product');
    }
    public function activeProducts() {
         $products = $this->products;
        return $products->whereLoose('id_status', config('const.status.active'));
    }
    public function activeParentProducts() {
        $products = $this->products;
        return $products->whereLoose('id_status', config('const.status.active'))
                    ->whereLoose('product.id_products', 0);
    }
    public function properties() {
        return $this->hasMany('App\Models\BuildingPropertyValue', 'id_buildings');
    }
    public function getProperties(){
        $properties = $this->properties;
        return $properties->pluck('value', 'id_building_properties');
    }
    public function getProperty($propertyId){
        $properties = $this->getProperties();
        return isset($properties[$propertyId]) ? $properties[$propertyId] : null;
    }
    public function getUnitNumbers() {

        $unitsInJson = $this->getProperty(config('const.building_property.unit_numbers'));
        return json_decode($unitsInJson);
    }




    public function tickets(){
      return $this->belongsToMany('App\Models\Ticket');
    }
}
