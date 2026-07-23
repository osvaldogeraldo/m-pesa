<?php

namespace BrilliantMind\MPesa\Contracts;

interface FakeContract
{
    /**
     * Enable fake mode and define the canned response.
     *
     * $status accepts an INS code ('INS-0', 'INS-2006') or a free form description.
     */
    public static function fake(int $responseCode = 200, string $status = ''): void;

    /**
     * Override the canned status/description for fake transactions.
     */
    public static function setStatus(string $status): void;

    /**
     * Override the canned HTTP-like response code for fake transactions.
     */
    public static function setResponseCode(int $code): void;
}
