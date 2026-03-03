<?php

namespace BrilliantMind\MPesa\Providers;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\MPesa;
use Illuminate\Support\ServiceProvider;

class MPesaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file if running in console (for Laravel artisan)
        // if ($this->app->runningInConsole()) {
        //     $this->publishes([
        //         __DIR__ . '/../../config/mpesa.php' => config_path('mpesa.php'),
        //     ], 'config');
        // }

        Config::config(
            env('MPESA_API_KEY'),
            env('MPESA_PUBLIC_KEY'),
            env('MPESA_ENV', 'development'), // development ou production
            env('MPESA_SERVICE_PROVIDER_CODE', '171717'),
            env('MPESA_ORIGIN', 'developer.mpesa.vm.co.mz'),
            env('MPESA_INITIATOR_IDENTIFIER'),
            env('MPESA_SECURITY_CREDENTIAL')
);

        $this->publishes([
            __DIR__.'/../../config/mpesa.php' => config_path('mpesa.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge the package configuration with the app's configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/mpesa.php', 'mpesa'
        );

        // Register the MPesa class with the application
        $this->app->singleton('mpesa', function () {
            return new MPesa();
        });
    }
}
