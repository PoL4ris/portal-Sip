<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $fillable = ['id', 'id_apps', 'name', 'icon', 'url'];
}
