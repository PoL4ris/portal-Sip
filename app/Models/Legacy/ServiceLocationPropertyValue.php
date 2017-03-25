<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ServiceLocationPropertyValue extends Model
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
    protected $table = 'serviceLocationPropertyValues';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'VID';
}
