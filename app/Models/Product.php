<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * 
     * @return type
     */
    public function type() {
        return $this->hasOne('App\Models\Type', 'id_types');
    }
    
    public function parentProduct() {
        return $this->hasOne('App\Models\Product', 'id', 'id_products');
    }

    public function propertyValues() {
        return $this->hasMany('App\Models\ProductPropertyValue', 'id_products', 'id');
//        'country_id', 'user_id', 'id'
    }

//    countries
//        id - integer
//        name - string
//
//    users
//        id - integer
//        country_id - integer
//        name - string
//
//    posts
//        id - integer
//        user_id - integer
//        title - string

//    public function posts()
//    {
//        return $this->hasManyThrough(
//            'App\Post', 'App\User',
//            'country_id', 'user_id', 'id'
//        );
//    }
}
