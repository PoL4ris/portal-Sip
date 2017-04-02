<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class NetworkTabOld extends Model {

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
    protected $table = 'networkTab';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'NID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
//    public $timestamps = false;

}
