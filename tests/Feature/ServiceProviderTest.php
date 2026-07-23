<?php

namespace BrilliantMind\MPesa\Tests\Feature;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Facades\MPesa as MPesaFacade;
use BrilliantMind\MPesa\MPesa;
use BrilliantMind\MPesa\Providers\MPesaServiceProvider;
use BrilliantMind\MPesa\Tests\TestCase;
use Illuminate\Support\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_it_boots_the_service_provider(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(MPesaServiceProvider::class));
    }

    public function test_it_merges_the_package_config_without_publishing(): void
    {
        $this->assertSame('development', config('mpesa.environment'));
        $this->assertSame('171717', config('mpesa.service_provider_code'));
        $this->assertSame('developer.mpesa.vm.co.mz', config('mpesa.origin'));
        $this->assertSame('18352', config('mpesa.ports.c2b'));
    }

    public function test_the_container_binding_and_facade_resolve(): void
    {
        $this->assertInstanceOf(MPesa::class, $this->app->make('mpesa'));
        $this->assertInstanceOf(MPesa::class, $this->app->make(MPesa::class));
        $this->assertSame($this->app->make('mpesa'), $this->app->make(MPesa::class));
        $this->assertInstanceOf(MPesa::class, MPesaFacade::getFacadeRoot());
    }

    public function test_the_config_file_is_publishable(): void
    {
        $paths = ServiceProvider::pathsToPublish(MPesaServiceProvider::class, 'mpesa-config');

        $this->assertNotEmpty($paths);
        $this->assertContains(config_path('mpesa.php'), array_values($paths));
    }

    public function test_it_reads_the_credentials_from_env_without_any_manual_wiring(): void
    {
        config([
            'mpesa.api_key' => 'api-key-from-env',
            'mpesa.public_key' => self::samplePublicKey(),
            'mpesa.environment' => 'production',
            'mpesa.service_provider_code' => '900900',
        ]);

        $this->assertTrue(Config::isConfigured());
        $this->assertSame('api-key-from-env', Config::getApiKey());
        $this->assertSame('900900', Config::getServiceProviderCode());
        $this->assertSame('api.vm.co.mz', Config::getHost());
    }

    public function test_it_reads_the_public_key_from_a_file_when_the_env_value_is_empty(): void
    {
        $path = sys_get_temp_dir() . '/mpesa-public-key-' . getmypid() . '.txt';
        file_put_contents($path, self::samplePublicKey());

        config([
            'mpesa.api_key' => 'api-key',
            'mpesa.public_key' => '',
            'mpesa.public_key_path' => $path,
        ]);

        $this->assertTrue(Config::isConfigured());
        $this->assertSame(self::samplePublicKey(), Config::getPublicKey());

        @unlink($path);
    }
}
