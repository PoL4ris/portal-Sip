<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\SendMail;


class SendMailProvider extends ServiceProvider
{
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
      $this->app->singleton('sendmail', function() { return new SendMail; });
    }
}
