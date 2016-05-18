<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNote extends Model
{
/**
     * 
     * @return type
     */
    public function ticket() {

        return $this->hasOne('App\Models\Ticket', 'id_tickets');
    }

    /**
     * 
     * @return type
     */
    public function ticketHistory() {

        return $this->hasOne('App\Models\TicketHistory', 'id_history_tickets');
    }
    
    /**
     * 
     * @return type
     */
    public function user() {

        return $this->hasOne('App\Models\User', 'id_users');
    }

}
