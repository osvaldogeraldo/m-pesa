<?php

namespace BrilliantMind\MPesa\Tests;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\MPesa;
use BrilliantMind\MPesa\Providers\MPesaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::reset();
        MPesa::stopFaking();
    }

    protected function tearDown(): void
    {
        Config::reset();
        MPesa::stopFaking();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [MPesaServiceProvider::class];
    }

    /**
     * Mirrors the "aliases" entry composer package discovery registers.
     *
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'MPesa' => \BrilliantMind\MPesa\Facades\MPesa::class,
            'Mpesa' => \BrilliantMind\MPesa\Facades\MPesa::class,
        ];
    }

    /**
     * A throwaway RSA public key in the same single line base64 shape the
     * M-Pesa developer portal hands out. Its private half was discarded.
     */
    public static function samplePublicKey(): string
    {
        return 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAogWeAJwRsiG3z51PPKQK'
            . 'KPGOe11aP5+3k8USTLZnWFjPiPuLXnQ+dtFpbpVJMcta/M5AeLcOGTN1ttU0nscs'
            . 'pjOBOCELgfQ3xny2XFeV/EmYp+WERTRhSrKXsLmRx9p+hW6XsEl4tu2FA39TAURR'
            . 'Q40D3+aP3RQylnqQfeul4XRDwJkSbik2inZYMxyQgAZlZmziR9JFks0blfc0KeEF'
            . 'unhJ/fBWQQi+xMRnVXv5hn1EdkmWy6BtDiYyxawaVV6K+WKA4sTvNJX2aLLe8LE3'
            . 'grZoxYB7rn9wqNiMx6DKpIkvo+hl8FtMD/yADHFGBlY1otkoUhdhMDbiEHbCO/DG'
            . 'FwIDAQAB';
    }
}
