<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
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
    public function reason() {

        return $this->hasOne('App\Models\Reason', 'id_reasons');
    }

    /**
     * 
     * @return type
     */
    public function ticketNote() {

        return $this->hasOne('App\Models\TicketNote', 'id_ticket_notes');
    }

    /**
     * 
     * @return type
     */
    public function user() {

        return $this->hasOne('App\Models\User', 'id_user');
    }


    /**
     * 
     * @return type
     */
    public function userAssigned() {

        return $this->hasOne('App\Models\User', 'id_user');
    }
}
