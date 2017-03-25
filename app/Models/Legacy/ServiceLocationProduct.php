<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ServiceLocationProduct extends Model
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
    protected $table = 'serviceLocationProducts';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SLPID';

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
    public function product() {
        return $this->hasOne('App\Models\Legacy\ProductOld', 'ProdID', 'ProdID');
    }

    /**
     *
     * @return type
     */
    public function serviceLocation() {
        return $this->hasOne('App\Models\Legacy\ServiceLocation', 'LocID', 'LocID');
    }

}
