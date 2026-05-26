<?php

namespace BrilliantMind\MPesa\Contracts\Token;

interface ParserContract
{
    /**
     * Encrypt the API key with the M-Pesa public key and return a base64 token.
     *
     * @param string $apiKey    The plain API key provided by Vodacom.
     * @param string $publicKey The base64-encoded RSA public key (without PEM headers).
     */
    public static function parse(string $apiKey, string $publicKey): string;
}
