<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class CustomerProductOld extends Model {

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
    protected $table = 'customerProducts';


    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CSID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
//    public $timestamps = false;

    /**
     *
     * @return type
     */
    public function customer() {
        return $this->belongsTo('App\Models\Legacy\CustomerOld', 'CID');
    }

    /**
     *
     * @return type
     */
    public function product() {
        return $this->hasOne('App\Models\Legacy\ProductOld', 'ProdID', 'ProdID');
    }

}
