<?php

namespace BrilliantMind\MPesa\Facades;

use BrilliantMind\MPesa\Transaction;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Transaction c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction b2b(float $amount, string $receiverPartyCode, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction transaction(string $transactionReference, string $thirdPartyReference)
 * @method static Transaction reversal(float $amount, string $transactionID, string $thirdPartyReference)
 * @method static void fake(int $responseCode = 200, string $status = 'INS-0')
 * @method static void fakeWith(array $payload, int $responseCode = 200)
 * @method static void stopFaking()
 * @method static array recorded()
 * @method static bool isConfigured()
 * @method static string getEnvironment()
 * @method static string getHost()
 *
 * @see \BrilliantMind\MPesa\MPesa
 */
class MPesa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa';
    }
}
