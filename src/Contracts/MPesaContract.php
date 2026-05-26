<?php

namespace BrilliantMind\MPesa\Contracts;

use BrilliantMind\MPesa\Transaction;

interface MPesaContract
{
    /**
     * Initiates a customer to business (C2B) transaction on the M-Pesa API.
     */
    public function c2b(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference
    ): Transaction;

    /**
     * Initiates a business to business (B2B) transaction on the M-Pesa API.
     */
    public function b2b(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference
    ): Transaction;

    /**
     * Initiates a business to customer (B2C) transaction on the M-Pesa API.
     */
    public function b2c(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference
    ): Transaction;

    /**
     * Initiates a reversal transaction on the M-Pesa API.
     */
    public function reversal(
        float $amount,
        string $transactionID,
        string $thirdPartyReference
    ): Transaction;

    /**
     * Query a transaction in the M-Pesa API.
     */
    public function transaction(
        string $transactionReference,
        string $thirdPartyReference
    ): Transaction;
}
