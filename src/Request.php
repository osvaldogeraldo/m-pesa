<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Contracts\MPesaContract;
use BrilliantMind\MPesa\Exceptions\ConnectionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;

class Request implements MPesaContract
{
    protected string $host;

    protected string $origin;

    protected string $token;

    protected string $serviceProviderCode;

    protected string $initiatorIdentifier;

    protected string $securityCredential;

    /**
     * When set, overrides the port of every operation.
     */
    protected string $portOverride;

    /**
     * Port used by each operation.
     *
     * @var array<string, string>
     */
    protected array $ports;

    protected float $timeout;

    protected float $connectTimeout;

    protected bool $verifySsl;

    /**
     * @param array{ports?: array<string, string>, timeout?: float, connect_timeout?: float, verify_ssl?: bool} $options
     */
    public function __construct(
        string $host,
        string $origin,
        string $token,
        string $serviceProviderCode,
        string $initiatorIdentifier = '',
        string $securityCredential = '',
        string $portOverride = '',
        array $options = []
    ) {
        $this->host = $host;
        $this->origin = $origin;
        $this->token = $token;
        $this->serviceProviderCode = $serviceProviderCode;
        $this->initiatorIdentifier = $initiatorIdentifier;
        $this->securityCredential = $securityCredential;
        $this->portOverride = $portOverride;
        $this->ports = array_map('strval', $options['ports'] ?? []) + Config::DEFAULT_PORTS;
        $this->timeout = (float) ($options['timeout'] ?? 90.0);
        $this->connectTimeout = (float) ($options['connect_timeout'] ?? 30.0);
        $this->verifySsl = (bool) ($options['verify_ssl'] ?? true);
    }

    /**
     * Initiates a customer to business (c2b) transaction on the M-Pesa API.
     *
     * @throws ConnectionException|GuzzleException
     */
    public function c2b(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return $this->call('c2b', 'POST', '/ipg/v1x/c2bPayment/singleStage/', [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
        ]);
    }

    /**
     * Initiates a business to business (b2b) transaction on the M-Pesa API.
     *
     * @throws ConnectionException|GuzzleException
     */
    public function b2b(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return $this->call('b2b', 'POST', '/ipg/v1x/b2bPayment/', [
            'input_TransactionReference' => $transactionReference,
            'input_PrimaryPartyCode' => $this->serviceProviderCode,
            'input_ReceiverPartyCode' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    /**
     * Initiates a business to customer (b2c) transaction on the M-Pesa API.
     *
     * @throws ConnectionException|GuzzleException
     */
    public function b2c(float $amount, string $msisdn, string $transactionReference, $thirdPartyReference): Transaction
    {
        return $this->call('b2c', 'POST', '/ipg/v1x/b2cPayment/', [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
        ]);
    }

    /**
     * Initiates a reversal transaction on the M-Pesa API.
     *
     * @throws ConnectionException|GuzzleException
     */
    public function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return $this->call('reversal', 'PUT', '/ipg/v1x/reversal/', [
            'input_Amount' => $amount,
            'input_TransactionID' => $transactionID,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
            'input_InitiatorIdentifier' => $this->initiatorIdentifier,
            'input_SecurityCredential' => $this->securityCredential,
        ]);
    }

    /**
     * Query the current status of a transaction.
     *
     * @throws ConnectionException|GuzzleException
     */
    public function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->call('status', 'GET', '/ipg/v1x/queryTransactionStatus/', [
            'input_QueryReference' => $transactionReference,
            'input_ThirdPartyReference' => $thirdPartyReference,
            'input_ServiceProviderCode' => $this->serviceProviderCode,
        ]);
    }

    /**
     * Perform one call against the gateway and normalize whatever comes back.
     *
     * @param array<string, mixed> $data
     * @throws ConnectionException|GuzzleException
     */
    protected function call(string $operation, string $method, string $uri, array $data): Transaction
    {
        $port = $this->portFor($operation);
        $isGet = $method === 'GET';

        $request = new \GuzzleHttp\Psr7\Request(
            $method,
            $isGet ? $uri . '?' . http_build_query($data) : $uri,
            [
                'Content-Type' => 'application/json',
                'origin' => $this->origin,
                'Authorization' => 'Bearer ' . $this->token,
            ],
            $isGet ? null : json_encode($data)
        );

        try {
            // http_errors is disabled, so 4xx/5xx come back as a normal response
            // carrying the M-Pesa error payload instead of blowing up.
            $response = $this->client($port)->send($request);
        } catch (TransferException $e) {
            // Covers connection refused, DNS failures and timeouts.
            throw ConnectionException::to($this->host, $port, $e);
        }

        return $this->toTransaction($response);
    }

    /**
     * Port used by a given operation.
     */
    protected function portFor(string $operation): string
    {
        if ($this->portOverride !== '') {
            return $this->portOverride;
        }

        return $this->ports[$operation] ?? Config::DEFAULT_PORTS[$operation] ?? '';
    }

    protected function client(string $port): Client
    {
        return new Client([
            'base_uri' => 'https://' . $this->host . ':' . $port,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify' => $this->verifySsl,
            'http_errors' => false,
        ]);
    }

    /**
     * Turn a PSR-7 response into a Transaction, tolerating empty or non JSON bodies.
     */
    protected function toTransaction(ResponseInterface $response): Transaction
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            $decoded = [
                'output_ResponseCode' => 'INS-23',
                'output_ResponseDesc' => $body === ''
                    ? 'Empty response from the M-Pesa gateway (HTTP ' . $response->getStatusCode() . ')'
                    : 'Unexpected response from the M-Pesa gateway (HTTP ' . $response->getStatusCode() . ')',
            ];
        }

        return new Transaction($decoded, $response->getStatusCode(), $body);
    }
}
