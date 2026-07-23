<?php

namespace BrilliantMind\MPesa\Exceptions;

use Throwable;

/**
 * Thrown when the M-Pesa gateway could not be reached at all.
 */
class ConnectionException extends MPesaException
{
    public static function to(string $host, string $port, Throwable $previous): self
    {
        return new self(
            "Could not reach the M-Pesa gateway at https://{$host}:{$port}. " .
            'The gateway uses non standard ports (18345, 18349, 18352, 18353, 18354); make sure your ' .
            'server or hosting provider allows outbound traffic to them. Original error: ' . $previous->getMessage(),
            (int) $previous->getCode(),
            $previous
        );
    }
}
