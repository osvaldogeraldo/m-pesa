<?php

namespace BrilliantMind\MPesa\Contracts;

interface TransactionContract
{
    /**
     * Construct transaction from mpesa api.
     *
     * @param array $response
     */
    public function __construct(array $response);

    /**
     * Get transaction response code.
     *
     * @return string
     */
    public function getResponseCode(): string;


    /**
     * Get transaction id.
     *
     * @return string
     */
    public function getTransactionID(): string;

    /**
     * Get transaction conversation id.
     *
     * @return string
     */
    public function getConversationID(): string;

    /**
     * Get transaction description.
     *
     * @return string
     */
    public function getDescription(): string;


    /**
     * Get transaction reference.
     *
     * @return string
     */
    public function getThirdPartReference(): string;

    /**
     * Get transaction message.
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key);
}