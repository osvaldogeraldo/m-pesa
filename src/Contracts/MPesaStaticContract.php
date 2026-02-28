<?php

namespace BrilliantMind\MPesa\Contracts;

use BrilliantMind\MPesa\Transaction;

interface MPesaStaticContract
{
    /**
     * Initiates a customer to business (c2b) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function c2b(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference): Transaction;

    /**
     * Initiates a customer to business (b2b) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function b2b(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference): Transaction;

    /**
     * Initiates a business to business (b2c) transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $msisdn
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function b2c(
        float $amount,
        string $msisdn,
        string $transactionReference,
        string $thirdPartyReference): Transaction;

    /**
     * Initiates a reversal transaction on the M-Pesa API.
     *
     * @param float $amount
     * @param string $transactionID
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function reversal(
        float $amount,
        string $transactionID,
        string $thirdPartyReference): Transaction;

    /**
     * Get transaction in M-Pesa API.
     *
     * @param string $transactionReference
     * @param string $thirdPartyReference
     * @return Transaction
     */
    public static function transaction(
        string $transactionReference,
        string $thirdPartyReference): Transaction;
}