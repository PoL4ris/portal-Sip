<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class SalesPropertyReminder extends Model
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
    protected $table = 'salesPropertyReminders';

    /* The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ReminderID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
