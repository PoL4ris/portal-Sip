<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ProductPropertyValueOld extends Model
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
    protected $table = 'productPropertyValues';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'VID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
