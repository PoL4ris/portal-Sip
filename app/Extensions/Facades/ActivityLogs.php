<?php

namespace App\Extensions\Facades;

use Illuminate\Support\Facades\Facade;


class ActivityLogs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'activitylogs';
    }
}
