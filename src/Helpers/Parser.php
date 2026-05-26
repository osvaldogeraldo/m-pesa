<?php

namespace BrilliantMind\MPesa\Helpers;

use BrilliantMind\MPesa\Contracts\Token\ParserContract;
use RuntimeException;

class Parser implements ParserContract
{
    /**
     * Encrypt the API key with the M-Pesa public key and return a base64 token.
     *
     * @param string $apiKey    The plain API key provided by Vodacom.
     * @param string $publicKey The base64-encoded RSA public key (without PEM headers).
     */
    public static function parse(string $apiKey, string $publicKey): string
    {
        $certificate = self::keysToCertificate($publicKey);

        $key = openssl_get_publickey($certificate);
        if ($key === false) {
            throw new RuntimeException('Invalid M-Pesa public key provided to Parser::parse().');
        }

        $token = '';
        $ok = openssl_public_encrypt($apiKey, $token, $key, OPENSSL_PKCS1_PADDING);
        if ($ok === false) {
            throw new RuntimeException('Failed to encrypt M-Pesa API key with the given public key.');
        }

        return base64_encode($token);
    }

    protected static function keysToCertificate(string $publicKey): string
    {
        $certificate = "-----BEGIN PUBLIC KEY-----\n";
        $certificate .= wordwrap($publicKey, 60, "\n", true);
        $certificate .= "\n-----END PUBLIC KEY-----";
        return $certificate;
    }
}
