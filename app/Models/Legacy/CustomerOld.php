<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class CustomerOld extends Model {

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
    protected $table = 'customers';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     *
     * @return type
     */
    public function tickets() {

        return $this->hasMany('App\Models\Legacy\SupportTicket', 'CID', 'CID');
    }

    /**
     *
     * @return type
     */
    public function servicelocations() {

        return $this->hasOne('App\Models\Legacy\ServiceLocation', 'LocID', 'LocID');
    }

}
