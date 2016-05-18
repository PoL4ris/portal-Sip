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

    //    /**
    //     * 
    //     * @return type
    //     */
    //    public function customer1() {
    //
    //        return $this->hasMany('App\Models\Portal\SupportTicket', 'CID', 'CID');
    //    }


}
