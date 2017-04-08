<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use DB;

class ServiceLocation extends Model {

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
    protected $table = 'serviceLocation';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'LocID';

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
    public function customers() {

        return $this->hasMany('App\Models\Legacy\CustomerOld', 'LocID', 'LocID');
    }

    /**
     *
     * @return type
     */
    public function tickets() {

        return $this->hasManyThrough('App\Models\Legacy\SupportTicket', 'App\Models\Legacy\CustomerOld', 'LocID', 'CID', 'LocID');
    }

    /**
     *
     * @return type
     */
    public function products() {

        return $this->belongsToMany('App\Models\Legacy\ProductOld', 'serviceLocationProducts', 'LocID', 'ProdID');
    }

    /**
     *
     * @return type
     */
    public function internetProducts() {

        return $this->belongsToMany('App\Models\Legacy\ProductOld', 'serviceLocationProducts', 'LocID', 'ProdID')
            ->where('ProdType','Internet');
    }

    /**
     *
     * @param string $shortName
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTicketsByShortName($shortName, $startDate, $endDate) {

        $serviceLocation = ServiceLocation::with(['tickets' => function($query) use($startDate, $endDate) {
            $query->where('DateCreated', '>=', $startDate)
                ->where('DateCreated', '<', $endDate);
        }])->where('ShortName', '=', $shortName)->first();

        return $serviceLocation;
    }

    public function getTicketsByLocID($locID, $startDate, $endDate) {

        $serviceLocation = ServiceLocation::with(['tickets' => function($query) use($startDate, $endDate) {
            $query->where('DateCreated', '>=', $startDate)
                ->where('DateCreated', '<', $endDate);
        }])->where('LocID', '=', $locID)->first();

        return $serviceLocation;
    }

    public function getNewTicketsByLocID($locID, $startDate, $endDate) {

        $serviceLocation = ServiceLocation::with(['tickets' => function($query) use($startDate, $endDate) {
            $query->where('DateCreated', '>=', $startDate)
                ->where('DateCreated', '<', $endDate);
        }])->where('LocID', '=', $locID)->first();

        return $serviceLocation;
    }

}
