<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PaymentMethod;
use App\Extensions\SIPBilling;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        PaymentMethod::saving(
            function ($paymentMethod)
            {
                $sipBilling = new SIPBilling();

                return $sipBilling->updatePaymentMethod($paymentMethod);
            });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
