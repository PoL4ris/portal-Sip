<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;


class Building extends Model
{
    public function customer() {
        return $this->hasMany('App\Models\Customer', 'id_buildings', 'id');
    }

    public function address() {
        return $this->hasMany('App\Models\Address', 'id_buildings', 'id')
            ->whereNull('id_customers');
    }
    public function neighborhood() {
        return $this->hasOne('App\Models\Neighborhood', 'id', 'id_neighborhoods');
    }
    public function contacts() {
        return $this->hasMany('App\Models\BuildingContact', 'id_buildings', 'id');
    }
    public function products() {
        return $this->hasMany('App\Models\BuildingProduct', 'id_buildings', 'id')->with('product');
//        return $this->hasOne('App\Models\BuildingProduct', 'id', 'id_buildings');
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
        return $this->hasMany('App\Models\Building\BuildingPropertyValue', 'id_buildings');
    }
}
