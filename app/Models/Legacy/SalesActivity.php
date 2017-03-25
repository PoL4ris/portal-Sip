<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SalesActivity extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'old-portal';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'salesActivity';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SalesActivityID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
