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
 */
class Transaction implements TransactionContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Construct transaction from mpesa api.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->attributes = $response;
    }

    /**
     * Get transaction response code.
     *
     * @return string
     */
    public function getResponseCode(): string
    {
        return $this->responseCode;
    }

    /**
     * Get transaction id.
     *
     * @return string
     */
    public function getTransactionID(): string
    {
        return $this->transactionID;
    }

    /**
     * Get transaction conversation id.
     *
     * @return string
     */
    public function getConversationID(): string
    {
        return $this->conversationID;
    }

    /**
     * Get transaction description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->responseDescription;
    }

    /**
     * Get transaction reference.
     *
     * @return string
     */
    public function getThirdPartReference(): string
    {
        return $this->thirdPartyReference;
    }

    public function getMessage(): string
    {
        return Response::$codes[$this->attributes['output_ResponseCode']]['code'];
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'responseCode' => $this->attributes['output_ResponseCode'] ?? null,
            'transactionID' => $this->attributes['output_TransactionID'] ?? null,
            'conversationID' => $this->attributes['output_ConversationID'] ?? null,
            'responseDescription' => $this->attributes['output_ResponseDesc'] ?? null,
            'thirdPartyReference' => $this->attributes["output_ThirdPartyReference" ] ?? null,
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
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
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->toArray()[$key] ?? null;
    }
}