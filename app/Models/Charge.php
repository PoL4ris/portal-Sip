<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = ['id', 'name', 'address', 'description', 'details', 'amount', 'qty', 'id_customers', 'id_products', 'id_address', 'status', 'type', 'comment'];
    protected $guarded = [];

    public function customer()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'id_customers');
    }

    public function address()
    {
        return $this->hasOne('App\Models\Address', 'id', 'id_address');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Models\Invoice', 'id_invoices');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'id_users');
    }

    public function productDetail()
    {
        return $this->hasOne('App\Models\CustomerProduct', 'id', 'id_customer_products');
    }

    public function customerProduct()
    {
        return $this->belongsTo('App\Models\CustomerProduct', 'id_customer_products');
    }
}
