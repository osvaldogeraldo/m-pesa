<?php

namespace BrilliantMind\MPesa\Helpers;


use BrilliantMind\MPesa\Contracts\Token\ParserContract;

class Parser implements ParserContract
{
    /**
     * Parse public and private key into token.
     *
     * @param string $publicKey
     * @param string $privateKey
     * @return string
     */
    public static function parse(string $api_key, string $public_key): string
    {
        $key = self::keysToCertificate($public_key);
        $public_key = openssl_get_publickey($key);
        openssl_public_encrypt($api_key, $token, $public_key, OPENSSL_PKCS1_PADDING);
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