<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SalesPropertyImage extends Model {

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
    protected $table = 'salesPropertyImages';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ImageID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ImageType', 'Image', 'ImageSize', 'ImageCtgy', 'ImageName'];
}
