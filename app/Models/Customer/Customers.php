<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CID';
}

