<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReason extends Model
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
    protected $table = 'supportTicketReasons';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'RID';

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
    public function tickets() {
        return $this->belongsToMany('App\Models\Legacy\SupportTicket', 'RID', 'RID');
    }
}
