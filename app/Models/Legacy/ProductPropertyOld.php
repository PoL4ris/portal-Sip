<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ProductPropertyOld extends Model
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
    protected $table = 'productProperties';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'PropID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
//    public $timestamps = false;
}
