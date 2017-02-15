<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\ActivityLogger;

class ActivityLogServiceProvider extends ServiceProvider
{
    
//    protected $defer = true;
    
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('activitylog', function() { return new ActivityLogger; });
    }
}
