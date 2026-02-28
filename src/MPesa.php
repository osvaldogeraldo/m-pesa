<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Contracts\FakeContract;
use BrilliantMind\MPesa\Contracts\MPesaContract;
use BrilliantMind\MPesa\Contracts\MPesaStaticContract;
use BrilliantMind\MPesa\Helpers\Parser;
use BrilliantMind\MPesa\Config\Config;

class MPesa extends Config implements MPesaStaticContract, FakeContract
{
    /**
     * @var bool $test
     */
    protected static bool $fake = false;

    /**
     * @var string
     */
    protected static string $status = "";

    /**
     * @var int
     */
    protected static int $responseCode = 200;

    /**
     * @param int $responseCode
     * @param string $status
     */
    public static function fake(int $responseCode = 200, string $status = ""): void
    {
        self::$fake = true;
        self::$status = $status;
        self::$responseCode = $responseCode;
    }

    /**
     * @param string $status
     */
    public static function setStatus(string $status): void
    {
        self::$status = $status;
    }

    /**
     * @param int $code
     */
    public static function setResponseCode(int $code): void
    {
        self::$responseCode = $code;
    }
    /**
     * Initiates a customer to business (c2b) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return mixed
     */
    public static function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return (new static())->mPesa()->c2b($amount, $msisdn, $transactionReference, $thirdPartyReference);
    }

    /**
     * Initiates a customer to business (b2b) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param $thirdPartyReference
     * @return mixed
     */
    public static function b2b(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return (new static())->mPesa()->b2b($amount, $msisdn, $transactionReference, $thirdPartyReference);;
    }

    /**
     * Initiates a business to business (b2c) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param $thirdPartyReference
     * @return mixed
     */
    public static function b2c(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return (new static())->mPesa()->b2c($amount, $msisdn, $transactionReference, $thirdPartyReference);
    }

    /**
     * Get transaction in M-Pesa API.
     *
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return (new static())->mPesa()->transaction($transactionReference, $thirdPartyReference);
    }


    /**
     * Initiates a reversal transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $transactionID
     * @param string $thirdPartyReference
     * @return mixed
     */
    public static function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return (new static())->mPesa()->reversal($amount, $transactionID, $thirdPartyReference);
    }

    /**
     * @return MPesaContract
     */
    protected function mPesa()
    {
        $token = Parser::parse(self::getApiKey(), self::getPublicKey());
        $request = new Request(self::getHost(), self::getOrigin(), $token, self::getServiceProviderCode(), self::getInitiatorIdentifier(), self::getSecurityCredential());
        return $request;
    }
}
