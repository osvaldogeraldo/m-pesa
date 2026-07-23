<?php

namespace BrilliantMind\MPesa\Exceptions;

/**
 * Thrown when the package is used before the M-Pesa credentials are available.
 */
class MissingConfigurationException extends MPesaException
{
    /**
     * @param array<int, string> $missing
     */
    public static function forEnvKeys(array $missing): self
    {
        $keys = implode(', ', $missing);

        return new self(
            "M-Pesa is not configured yet: missing {$keys}. " .
            'Add the values to your .env file (or call MPesa::config(...) manually) and, ' .
            'if you cached the configuration, run "php artisan config:clear".'
        );
    }
}
