<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ServiceLocationProperty extends Model
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
    protected $table = 'serviceLocationProperties';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'PropID';

}
