<?php

namespace BrilliantMind\MPesa\Exceptions;

class InvalidEnvironmentException extends MPesaException
{
    public static function for(string $environment): self
    {
        return new self(
            "\"{$environment}\" is not a valid M-Pesa environment. " .
            'Use "development" (sandbox) or "production" in MPESA_ENVIRONMENT.'
        );
    }
}
