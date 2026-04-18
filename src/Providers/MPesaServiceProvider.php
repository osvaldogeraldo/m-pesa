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
            config('mpesa.api_key'),
            config('mpesa.public_key'),
            config('mpesa.environment', 'development'), // development ou production
            config('mpesa.service_provider_code', '171717'),
            config('mpesa.origin', 'developer.mpesa.vm.co.mz'),
            config('mpesa.initiatorIdentifier', ''),
            config('mpesa.securityCredential', ''),
            config('mpesa.host', ''),
            config('mpesa.port', '')
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
