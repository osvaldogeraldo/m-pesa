<?php

namespace BrilliantMind\MPesa\Providers;

use BrilliantMind\MPesa\MPesa;
use Illuminate\Support\ServiceProvider;

class MPesaServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     */
    public function register(): void
    {
        // Defaults live inside the package, so the app works without publishing anything.
        $this->mergeConfigFrom(__DIR__ . '/../../config/mpesa.php', 'mpesa');

        $this->app->singleton('mpesa', fn () => new MPesa());
        $this->app->alias('mpesa', MPesa::class);
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $config = [__DIR__ . '/../../config/mpesa.php' => config_path('mpesa.php')];

            // Publishing is optional; both tags point at the same file.
            $this->publishes($config, 'mpesa-config');
            $this->publishes($config, 'config');
        }

        // Credentials are read lazily on the first transaction (see Config::hydrateIfNeeded),
        // which keeps the package working with config:cache, queue workers and Octane.
        // Nothing else needs to happen here.
    }
}
