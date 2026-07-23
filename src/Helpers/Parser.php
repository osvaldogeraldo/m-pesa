<?php

namespace BrilliantMind\MPesa\Helpers;

use BrilliantMind\MPesa\Contracts\Token\ParserContract;
use BrilliantMind\MPesa\Exceptions\EncryptionException;

class Parser implements ParserContract
{
    /**
     * Build the bearer token by encrypting the api key with the M-Pesa public key.
     *
     * @throws EncryptionException
     */
    public static function parse(string $api_key, string $public_key): string
    {
        // Drain any error left behind by unrelated OpenSSL calls so the
        // diagnostics below only report what happened here.
        while (openssl_error_string()) {
            // no-op
        }

        $certificate = self::keysToCertificate($public_key);
        $key = openssl_pkey_get_public($certificate);

        if ($key === false) {
            throw EncryptionException::invalidPublicKey();
        }

        if (! openssl_public_encrypt($api_key, $token, $key, OPENSSL_PKCS1_PADDING)) {
            throw EncryptionException::encryptionFailed();
        }

        return base64_encode($token);
    }

    /**
     * Wrap a bare base64 key into a PEM certificate, leaving real PEM blocks untouched.
     */
    protected static function keysToCertificate(string $publicKey): string
    {
        $publicKey = trim(str_replace("\r\n", "\n", $publicKey));

        if (str_contains($publicKey, '-----BEGIN')) {
            return $publicKey;
        }

        $publicKey = (string) preg_replace('/\s+/', '', $publicKey);

        return "-----BEGIN PUBLIC KEY-----\n"
            . wordwrap($publicKey, 64, "\n", true)
            . "\n-----END PUBLIC KEY-----\n";
    }
}
