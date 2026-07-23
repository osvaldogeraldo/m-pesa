<?php

namespace BrilliantMind\MPesa\Tests\Unit;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Exceptions\InvalidEnvironmentException;
use BrilliantMind\MPesa\MPesa;
use BrilliantMind\MPesa\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * Regression: reading a typed static property before it was assigned used to raise
     * "Typed static property ... must not be accessed before initialization".
     */
    public function test_partial_configuration_does_not_fatal(): void
    {
        MPesa::config('api-key', 'public-key');

        $this->assertSame('api-key', Config::getApiKey());
        $this->assertSame('', Config::getInitiatorIdentifier());
        $this->assertSame('', Config::getSecurityCredential());
        $this->assertSame('', Config::getPort());
    }

    public function test_every_getter_is_safe_on_a_fresh_class(): void
    {
        Config::reset();

        $this->assertSame('', Config::getApiKey());
        $this->assertSame('', Config::getPublicKey());
        $this->assertSame('development', Config::getEnvironment());
        $this->assertSame('api.sandbox.vm.co.mz', Config::getHost());
        $this->assertSame('171717', Config::getServiceProviderCode());
        $this->assertFalse(Config::isConfigured());
    }

    public function test_the_host_follows_the_environment(): void
    {
        MPesa::config(environment: 'development');
        $this->assertSame('api.sandbox.vm.co.mz', Config::getHost());

        MPesa::config(environment: 'production');
        $this->assertSame('api.vm.co.mz', Config::getHost());

        MPesa::config(host: 'my-proxy.example.com');
        $this->assertSame('my-proxy.example.com', Config::getHost());
    }

    public function test_environment_aliases_are_accepted(): void
    {
        foreach (['sandbox', 'dev', 'testing', 'local'] as $alias) {
            MPesa::config(environment: $alias);
            $this->assertSame('development', Config::getEnvironment());
        }

        foreach (['prod', 'live', 'PRODUCTION'] as $alias) {
            MPesa::config(environment: $alias);
            $this->assertSame('production', Config::getEnvironment());
        }
    }

    public function test_an_unknown_environment_is_rejected(): void
    {
        $this->expectException(InvalidEnvironmentException::class);

        MPesa::config(environment: 'staging-ish');
    }

    public function test_ports_default_per_operation_and_honour_the_global_override(): void
    {
        $this->assertSame('18352', Config::getPortFor('c2b'));
        $this->assertSame('18345', Config::getPortFor('b2c'));
        $this->assertSame('18353', Config::getPortFor('status'));

        MPesa::config(port: '443');

        $this->assertSame('443', Config::getPortFor('c2b'));
        $this->assertSame('443', Config::getPortFor('reversal'));
    }

    public function test_a_public_key_pasted_with_line_breaks_is_normalized(): void
    {
        MPesa::config(public_key: "MIIBIjANBgkq\n hkiG9w0BAQEF\r\nAAOCAQ8A");

        $this->assertSame('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A', Config::getPublicKey());
    }
}
