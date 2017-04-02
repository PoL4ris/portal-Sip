<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class BillingTransactionLogOld extends Model
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
    protected $table = 'billingTransactionLog';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'LogID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
//    public $timestamps = false;

}
