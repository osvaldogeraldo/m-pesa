<?php

namespace BrilliantMind\MPesa\Contracts;

interface FakeContract
{
    /**
     * Enable fake mode and define the canned HTTP-like response code.
     */
    public function fake(int $responseCode = 200, string $status = ''): void;

    /**
     * Override the canned status/description for fake transactions.
     */
    public function setStatus(string $status): void;

    /**
     * Override the canned HTTP-like response code for fake transactions.
     */
    public function setResponseCode(int $code): void;
}
