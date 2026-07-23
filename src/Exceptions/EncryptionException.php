<?php

namespace BrilliantMind\MPesa\Exceptions;

/**
 * Thrown when the bearer token cannot be derived from the api key + public key pair.
 */
class EncryptionException extends MPesaException
{
    public static function invalidPublicKey(): self
    {
        return new self(
            'The M-Pesa public key could not be read. Copy the whole "Public Key" value from the ' .
            'M-Pesa developer portal into MPESA_PUBLIC_KEY (a single line, no spaces), or point ' .
            'MPESA_PUBLIC_KEY_PATH to a file containing it. OpenSSL said: ' . self::opensslErrors()
        );
    }

    public static function encryptionFailed(): self
    {
        return new self(
            'Could not encrypt the M-Pesa api key with the given public key. OpenSSL said: ' . self::opensslErrors()
        );
    }

    protected static function opensslErrors(): string
    {
        $errors = [];

        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }

        return $errors === [] ? 'no further details.' : implode(' | ', $errors);
    }
}
