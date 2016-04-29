<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Model;

class networkNodes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'networkNodes';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'NodeID';

    public $timestamps = false;
    
    public function serviceLocation() {
        return $this->belongsTo('App\Models\Network\ServiceLocation', 'LocID');
    }
    
}
