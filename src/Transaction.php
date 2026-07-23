<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Contracts\TransactionContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use JsonSerializable;

/**
 * @property string $responseCode
 * @property string $transactionID
 * @property string $conversationID
 * @property string $thirdPartyReference
 * @property string $responseDescription
 * @property string $transactionStatus
 */
class Transaction implements TransactionContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * The raw payload returned by the gateway.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * HTTP status of the response that produced this transaction.
     */
    protected int $httpStatus;

    /**
     * Untouched response body, kept for logging when the payload is not valid JSON.
     */
    protected string $rawBody;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response, int $httpStatus = 200, string $rawBody = '')
    {
        $this->attributes = $response;
        $this->httpStatus = $httpStatus;
        $this->rawBody = $rawBody;
    }

    /**
     * Get transaction response code (for example "INS-0").
     */
    public function getResponseCode(): string
    {
        return (string) ($this->attributes['output_ResponseCode'] ?? '');
    }

    /**
     * Get transaction id.
     */
    public function getTransactionID(): string
    {
        return (string) ($this->attributes['output_TransactionID'] ?? '');
    }

    /**
     * Get transaction conversation id.
     */
    public function getConversationID(): string
    {
        return (string) ($this->attributes['output_ConversationID'] ?? '');
    }

    /**
     * Get the description sent by the gateway.
     */
    public function getDescription(): string
    {
        return (string) ($this->attributes['output_ResponseDesc'] ?? '');
    }

    /**
     * Get transaction reference.
     */
    public function getThirdPartReference(): string
    {
        return (string) ($this->attributes['output_ThirdPartyReference'] ?? '');
    }

    /**
     * Status returned by the "query transaction status" endpoint.
     */
    public function getTransactionStatus(): string
    {
        return (string) (
            $this->attributes['output_ResponseTransactionStatus']
            ?? $this->attributes['output_TransactionStatus']
            ?? ''
        );
    }

    /**
     * Human readable description of the response code.
     */
    public function getMessage(): string
    {
        $description = $this->getDescription();

        return $description !== '' ? $description : Response::message($this->getResponseCode());
    }

    /**
     * True when M-Pesa accepted the operation (INS-0).
     */
    public function isSuccessful(): bool
    {
        return Response::isSuccessful($this->getResponseCode());
    }

    public function failed(): bool
    {
        return ! $this->isSuccessful();
    }

    /**
     * HTTP status of the gateway response.
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * HTTP status that best represents the M-Pesa response code.
     */
    public function getStatusCode(): int
    {
        return Response::status($this->getResponseCode());
    }

    /**
     * The untouched payload, useful for logging and debugging.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->attributes;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Convert the transaction to a normalized array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'successful' => $this->isSuccessful(),
            'responseCode' => $this->attributes['output_ResponseCode'] ?? null,
            'transactionID' => $this->attributes['output_TransactionID'] ?? null,
            'conversationID' => $this->attributes['output_ConversationID'] ?? null,
            'responseDescription' => $this->attributes['output_ResponseDesc'] ?? null,
            'thirdPartyReference' => $this->attributes['output_ThirdPartyReference'] ?? null,
            'transactionStatus' => $this->getTransactionStatus() ?: null,
            'message' => $this->getMessage(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  int  $options
     * @throws JsonEncodingException
     */
    public function toJson($options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Dynamically retrieve attributes, both normalized and raw ones.
     */
    public function __get(string $key)
    {
        return $this->toArray()[$key] ?? $this->attributes[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return $this->__get($key) !== null;
    }
}
