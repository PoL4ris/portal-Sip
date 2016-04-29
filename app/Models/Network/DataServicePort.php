<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Model;

class DataServicePort extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dataServicePorts';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'PortID';
    
    public $timestamps = false;
    
    public function networkNode() {
        return $this->belongsTo('App\Models\Network\networkNodes', 'NodeID');
    }

//    public function statDates() {
//        return $this->hasMany('App\ClientStat', 'client_id')->select(['id', 'client_id', 'month', 'year'])
//            ->orderBy('year', 'desc')
//            ->orderBy('month', 'desc');
//        ;
//    }
//
//    public function activeGraphDates() {
//        return $this->hasMany('App\ClientGraph', 'client_id')->select(['id', 'client_id', 'month', 'year'])
//            ->where('status', '=', 'active')
//            ->orderBy('year', 'desc')
//            ->orderBy('month', 'desc');
//    }
//
//    public function activeStatDates() {
//        return $this->hasMany('App\ClientStat', 'client_id')->select(['id', 'client_id', 'month', 'year'])
//            ->where('status', '=', 'active')
//            ->orderBy('year', 'desc')
//            ->orderBy('month', 'desc');
//    }
//
//    public function users()
//    {
//        return $this->belongsToMany('App\User');
//    }
}
