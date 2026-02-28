<?php

namespace BrilliantMind\MPesa\Providers;

use Illuminate\Support\ServiceProvider;
use BrilliantMind\MPesa\MPesa;

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
