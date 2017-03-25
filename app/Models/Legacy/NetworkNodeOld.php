<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class NetworkNodeOld extends Model {

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
    protected $table = 'networkNode';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'NodeID';

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
    public function serviceLocation() {
        return $this->belongsTo('App\Models\Legacy\ServiceLocation', 'LocID');
    }

}
