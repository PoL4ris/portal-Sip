<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingTicket extends Model
{
  protected $table = 'building_ticket';

  protected $fillable = ['building_id', 'ticket_id'];

}