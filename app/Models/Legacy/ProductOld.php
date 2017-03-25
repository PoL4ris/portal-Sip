<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ProductOld extends Model
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
    protected $table = 'products';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ProdID';

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
    public function serviceLocationProperties() {
        return $this->belongsToMany('App\Models\Legacy\ServiceLocationProperty', 'ProdID', 'ProdID');
    }

}
