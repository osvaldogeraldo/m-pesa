<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Contracts\FakeContract;
use BrilliantMind\MPesa\Contracts\MPesaContract;
use BrilliantMind\MPesa\Contracts\MPesaStaticContract;
use BrilliantMind\MPesa\Helpers\Parser;

class MPesa extends Config implements MPesaStaticContract, FakeContract
{
    /**
     * Queue a canned response so no HTTP call reaches the gateway.
     *
     * Kept backwards compatible: fake() with no arguments returns a successful
     * transaction; fakeWith() gives full control over the payload.
     */
    public static function fake(int $responseCode = 200, string $status = Response::SUCCESS): void
    {
        Request::fake([
            'output_ResponseCode' => $status !== '' ? $status : Response::SUCCESS,
            'output_ResponseDesc' => Response::message($status !== '' ? $status : Response::SUCCESS),
            'output_TransactionID' => 'FAKE-TRANSACTION-ID',
            'output_ConversationID' => 'FAKE-CONVERSATION-ID',
            'output_ThirdPartyReference' => 'FAKE-THIRD-PARTY-REF',
        ], $responseCode);
    }

    /**
     * Queue an exact payload to be returned by the next call.
     *
     * @param array<string, mixed> $payload
     */
    public static function fakeWith(array $payload, int $responseCode = 200): void
    {
        Request::fake($payload, $responseCode);
    }

    public static function stopFaking(): void
    {
        Request::stopFaking();
    }

    public static function isFaking(): bool
    {
        return Request::isFaking();
    }

    /**
     * Requests captured while faking.
     *
     * @return array<int, array{operation: string, uri: string, payload: array<string, mixed>}>
     */
    public static function recorded(): array
    {
        return Request::recorded();
    }

    /**
     * Kept for backwards compatibility with FakeContract.
     */
    public static function setStatus(string $status): void
    {
        self::fake(200, $status);
    }

    /**
     * Kept for backwards compatibility with FakeContract.
     */
    public static function setResponseCode(int $code): void
    {
        self::fake($code);
    }

    /**
     * Initiates a customer to business (c2b) transaction on the M-Pesa API.
     */
    public static function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->c2b($amount, static::normalizeMsisdn($msisdn), $transactionReference, $thirdPartyReference);
    }

    /**
     * Initiates a business to business (b2b) transaction on the M-Pesa API.
     *
     * $receiverPartyCode is the shortcode of the receiving business.
     */
    public static function b2b(float $amount, string $receiverPartyCode, string $transactionReference, $thirdPartyReference): Transaction
    {
        return static::gateway()->b2b($amount, $receiverPartyCode, $transactionReference, $thirdPartyReference);
    }

    /**
     * Initiates a business to customer (b2c) transaction on the M-Pesa API.
     */
    public static function b2c(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return static::gateway()->b2c($amount, static::normalizeMsisdn($msisdn), $transactionReference, $thirdPartyReference);
    }

    /**
     * Query the current status of a transaction.
     */
    public static function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->transaction($transactionReference, $thirdPartyReference);
    }

    /**
     * Initiates a reversal transaction on the M-Pesa API.
     */
    public static function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return static::gateway()->reversal($amount, $transactionID, $thirdPartyReference);
    }

    /**
     * Build a ready to use gateway client from the current configuration.
     */
    public static function gateway(): MPesaContract
    {
        static::ensureConfigured();

        return new Request(
            static::getHost(),
            static::getOrigin(),
            Parser::parse(static::getApiKey(), static::getPublicKey()),
            static::getServiceProviderCode(),
            static::getInitiatorIdentifier(),
            static::getSecurityCredential(),
            static::getPort(),
            [
                'ports' => static::getPorts(),
                'timeout' => static::getTimeout(),
                'connect_timeout' => static::getConnectTimeout(),
                'verify_ssl' => static::shouldVerifySsl(),
            ]
        );
    }

    /**
     * Accept the phone number the way people actually type it and hand M-Pesa
     * the 258XXXXXXXXX format it expects.
     */
    public static function normalizeMsisdn(string $msisdn): string
    {
        $digits = (string) preg_replace('/\D+/', '', $msisdn);

        // 00258... -> 258...
        if (str_starts_with($digits, '00258')) {
            $digits = substr($digits, 2);
        }

        // 0841234567 -> 841234567
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // 841234567 -> 258841234567
        if (strlen($digits) === 9 && str_starts_with($digits, '8')) {
            $digits = '258' . $digits;
        }

        return $digits;
    }

    /**
     * @deprecated Use gateway() instead.
     */
    protected function mPesa(): MPesaContract
    {
        return static::gateway();
    }
}
