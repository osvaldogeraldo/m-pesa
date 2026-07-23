<?php

namespace BrilliantMind\MPesa\Providers;

use BrilliantMind\MPesa\Facades\MPesa as MPesaFacade;
use BrilliantMind\MPesa\MPesa;
use Illuminate\Foundation\AliasLoader;
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

        $this->registerAliases();
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

    /**
     * Both spellings have shipped in the wild, so both keep working.
     * Composer package discovery already registers "MPesa"; this covers apps
     * that register the provider by hand and the older "Mpesa" alias.
     */
    protected function registerAliases(): void
    {
        if (! class_exists(AliasLoader::class)) {
            return;
        }

        $loader = AliasLoader::getInstance();

        foreach (['MPesa', 'Mpesa'] as $alias) {
            if (! class_exists($alias, false)) {
                $loader->alias($alias, MPesaFacade::class);
            }
        }
    }
}
