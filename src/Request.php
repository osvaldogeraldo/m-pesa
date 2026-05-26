<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Contracts\MPesaContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Request implements MPesaContract
{
    protected string $host;
    protected string $origin;
    protected string $token;
    protected string $serviceProviderCode;
    protected string $initiatorIdentifier;
    protected string $securityCredential;

    public function __construct(
        string $host,
        string $origin,
        string $token,
        string $serviceProviderCode,
        string $initiatorIdentifier,
        string $securityCredential
    ) {
        $this->host = $host;
        $this->origin = $origin;
        $this->token = $token;
        $this->serviceProviderCode = $serviceProviderCode;
        $this->initiatorIdentifier = $initiatorIdentifier;
        $this->securityCredential = $securityCredential;
    }

    /**
     * Initiates a customer to business (C2B) transaction on the M-Pesa API.
     *
     * @throws GuzzleException
     */
    public function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        $data = [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN'       => $msisdn,
            'input_Amount'               => $amount,
            'input_ThirdPartyReference'  => $thirdPartyReference,
            'input_ServiceProviderCode'  => $this->serviceProviderCode,
        ];

        return $this->send('POST', '18352', '/ipg/v1x/c2bPayment/singleStage/', $data);
    }

    /**
     * Initiates a business to business (B2B) transaction on the M-Pesa API.
     *
     * @throws GuzzleException
     */
    public function b2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        $data = [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN'       => $msisdn,
            'input_Amount'               => $amount,
            'input_ThirdPartyReference'  => $thirdPartyReference,
            'input_ServiceProviderCode'  => $this->serviceProviderCode,
        ];

        return $this->send('POST', '18349', '/ipg/v1x/b2bPayment/', $data);
    }

    /**
     * Initiates a business to customer (B2C) transaction on the M-Pesa API.
     *
     * @throws GuzzleException
     */
    public function b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        $data = [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN'       => $msisdn,
            'input_Amount'               => $amount,
            'input_ThirdPartyReference'  => $thirdPartyReference,
            'input_ServiceProviderCode'  => $this->serviceProviderCode,
        ];

        return $this->send('POST', '18345', '/ipg/v1x/b2cPayment/', $data);
    }

    /**
     * Initiates a reversal transaction on the M-Pesa API.
     *
     * @throws GuzzleException
     */
    public function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        $data = [
            'input_Amount'              => $amount,
            'input_TransactionID'       => $transactionID,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
            'input_InitiatorIdentifier' => $this->initiatorIdentifier,
            'input_SecurityCredential'  => $this->securityCredential,
        ];

        return $this->send('PUT', '18354', '/ipg/v1x/reversal/', $data);
    }

    /**
     * Query a transaction in the M-Pesa API.
     *
     * @throws GuzzleException
     */
    public function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        $data = [
            'input_QueryReference'      => $transactionReference,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
        ];

        return $this->send('GET', '18353', '/ipg/v1x/queryTransactionStatus/?' . http_build_query($data), $data, false);
    }

    /**
     * Build the Guzzle client for the given port.
     */
    protected function client(string $port): Client
    {
        return new Client(['base_uri' => 'https://' . $this->host . ':' . $port]);
    }

    /**
     * Dispatch a request to M-Pesa and wrap the body in a Transaction.
     *
     * @param array<string, mixed> $data
     *
     * @throws GuzzleException
     */
    protected function send(string $method, string $port, string $uri, array $data, bool $withBody = true): Transaction
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Origin'        => $this->origin,
            'Authorization' => 'Bearer ' . $this->token,
        ];

        $body = $withBody ? json_encode($data) : null;
        $request = new \GuzzleHttp\Psr7\Request($method, $uri, $headers, $body);

        try {
            $response = $this->client($port)->send($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        return new Transaction($this->streamToArray($response));
    }

    /**
     * Convert a Guzzle response body into an array.
     */
    protected function streamToArray(?ResponseInterface $response): array
    {
        if ($response === null) {
            return [];
        }

        return $this->decode($response->getBody());
    }

    protected function decode(StreamInterface $stream): array
    {
        $decoded = json_decode((string) $stream, true);

        return is_array($decoded) ? $decoded : [];
    }
}
