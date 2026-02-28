<?php

namespace BrilliantMind\MPesa\Contracts;

interface FakeContract
{
    /**
     * @param int $responseCode
     * @param string|null $status
     */
    public static function fake(int $responseCode = 200, string $status = ''): void;

    /**
     * @param string $status
     */
    public static function setStatus(string $status): void;

    /**
     * @param int $code
     */
    public static function setResponseCode(int $code): void;
}