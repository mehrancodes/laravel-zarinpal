<?php

namespace Rasulian\ZarinPal;

use Illuminate\Support\ServiceProvider;

class ZarinPalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/zarinpal.php' => $this->app->configPath().'/zarinpal.php',
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
