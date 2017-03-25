<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class DataServicePort extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'old-portal';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'PortID';

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
    public function networkNode() {
        return $this->belongsTo('App\Models\Legacy\NetworkNodeOld', 'NodeID');
    }
}
