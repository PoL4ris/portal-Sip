<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SupportTicketHistory extends Model
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
    protected $table = 'supportTicketHistory';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'THID';

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
    public function reason() {
        return $this->hasOne('App\Models\Legacy\SupportTicketReason', 'RID', 'RID');
    }
}
