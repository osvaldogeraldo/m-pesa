<?php

namespace BrilliantMind\MPesa\Providers;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Facades\MPesaFacade;
use BrilliantMind\MPesa\MPesa;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class MPesaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/mpesa.php' => config_path('mpesa.php'),
        ], 'mpesa-config');

        Config::config(
            (string) config('mpesa.api_key', ''),
            (string) config('mpesa.public_key', ''),
            (string) config('mpesa.environment', 'development'),
            (string) config('mpesa.service_provider_code', '171717'),
            (string) config('mpesa.origin', 'developer.mpesa.vm.co.mz'),
            (string) config('mpesa.initiator_identifier', ''),
            (string) config('mpesa.security_credential', '')
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/mpesa.php', 'mpesa');

        $this->app->singleton('mpesa', fn () => new MPesa());

        AliasLoader::getInstance()->alias('Mpesa', MPesaFacade::class);
    }
}
