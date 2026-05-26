<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Contracts\FakeContract;
use BrilliantMind\MPesa\Contracts\MPesaContract;
use BrilliantMind\MPesa\Helpers\Parser;

class MPesa implements FakeContract
{
    protected bool $fake = false;

    protected string $fakeStatus = '';

    protected int $fakeResponseCode = 200;

    public function fake(int $responseCode = 200, string $status = ''): void
    {
        $this->fake = true;
        $this->fakeResponseCode = $responseCode;
        $this->fakeStatus = $status;
    }

    public function setStatus(string $status): void
    {
        $this->fakeStatus = $status;
    }

    public function setResponseCode(int $code): void
    {
        $this->fakeResponseCode = $code;
    }

    /**
     * Configurar credenciais em runtime (útil em cenários multi-tenant).
     *
     * Equivale a chamar Config::config(...) directamente. A facade `Mpesa::config(...)`
     * resolve para este método através do singleton registado pelo ServiceProvider.
     */
    public static function config(
        string $apiKey,
        string $publicKey,
        ?string $environment = null,
        ?string $serviceProviderCode = null,
        ?string $origin = null,
        ?string $initiatorIdentifier = null,
        ?string $securityCredential = null,
        ?string $host = null
    ): void {
        Config::config(
            $apiKey,
            $publicKey,
            $environment,
            $serviceProviderCode,
            $origin,
            $initiatorIdentifier,
            $securityCredential,
            $host
        );
    }

    public function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->client()->c2b($amount, $msisdn, $transactionReference, $thirdPartyReference);
    }

    public function b2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->client()->b2b($amount, $msisdn, $transactionReference, $thirdPartyReference);
    }

    public function b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->client()->b2c($amount, $msisdn, $transactionReference, $thirdPartyReference);
    }

    public function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->client()->transaction($transactionReference, $thirdPartyReference);
    }

    public function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return $this->client()->reversal($amount, $transactionID, $thirdPartyReference);
    }

    protected function client(): MPesaContract
    {
        if ($this->fake) {
            return new FakeRequest($this->fakeResponseCode, $this->fakeStatus);
        }

        $token = Parser::parse(Config::getApiKey(), Config::getPublicKey());

        return new Request(
            Config::getHost(),
            Config::getOrigin(),
            $token,
            Config::getServiceProviderCode(),
            Config::getInitiatorIdentifier(),
            Config::getSecurityCredential()
        );
    }
}
