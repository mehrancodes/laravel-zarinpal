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
            __DIR__.'/../config/laravel-zarinpal.php' => $this->app->configPath().'/laravel-zarinpal.php',
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
