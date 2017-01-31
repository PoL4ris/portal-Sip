<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * 
     * @return type
     */
    public function customer() {

        return $this->hasOne('App\Models\Customer', 'id_customers');
    }

    /**
     * 
     * @return type
     */
    public function type() {

        return $this->hasOne('App\Models\Type', 'id_types');
    }


    public function types() {

      return $this->belongsTo('App\Models\Contact','*', 'id_typess','*', 'customer', '*');
//      return $this->belongsToMany('App\Models\Contact','types', 'id', 'id_types','App\Models\type');
    }

    //    /**
    //     * 
    //     * @return type
    //     */
    //    public function customer1() {
    //
    //        return $this->hasMany('App\Models\Portal\SupportTicket', 'CID', 'CID');
    //    }


}
