<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Contracts\MPesaContract;

/**
 * Gateway falso usado por MPesa::fake(): devolve transacções pré-fabricadas
 * e regista os pedidos para asserções, sem tocar na rede.
 */
class FakeRequest implements MPesaContract
{
    /**
     * Payloads em fila colocados por MPesa::fakeWith().
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $queue = [];

    /**
     * @var array<int, array{operation: string, payload: array<string, mixed>}>
     */
    protected static array $recorded = [];

    public function __construct(
        protected int $responseCode = 200,
        protected string $status = ''
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function queue(array $payload): void
    {
        static::$queue[] = $payload;
    }

    public static function flush(): void
    {
        static::$queue = [];
        static::$recorded = [];
    }

    /**
     * @return array<int, array{operation: string, payload: array<string, mixed>}>
     */
    public static function recorded(): array
    {
        return static::$recorded;
    }

    public function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction('c2b', [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    public function b2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction('b2b', [
            'input_TransactionReference' => $transactionReference,
            'input_ReceiverPartyCode' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    public function b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction('b2c', [
            'input_TransactionReference' => $transactionReference,
            'input_CustomerMSISDN' => $msisdn,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    public function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction('reversal', [
            'input_TransactionID' => $transactionID,
            'input_Amount' => $amount,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    public function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction('status', [
            'input_QueryReference' => $transactionReference,
            'input_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function fakeTransaction(string $operation, array $payload): Transaction
    {
        static::$recorded[] = ['operation' => $operation, 'payload' => $payload];

        // Um payload em fila manda em tudo. Se só houver um, serve todas as chamadas.
        if (static::$queue !== []) {
            $queued = count(static::$queue) > 1 ? array_shift(static::$queue) : static::$queue[0];

            return new Transaction($queued, $this->responseCode, (string) json_encode($queued));
        }

        $insCode = $this->insCode();

        $attributes = [
            'output_ResponseCode' => $insCode,
            'output_ResponseDesc' => $this->description($insCode),
            'output_TransactionID' => 'FAKE-' . strtoupper(bin2hex(random_bytes(5))),
            'output_ConversationID' => 'FAKE-' . strtoupper(bin2hex(random_bytes(8))),
            'output_ThirdPartyReference' => $payload['input_ThirdPartyReference'] ?? '',
        ];

        return new Transaction($attributes, $this->responseCode, (string) json_encode($attributes));
    }

    /**
     * $status pode ser um código INS ou uma descrição livre; só no primeiro caso
     * é que manda no código de resposta.
     */
    protected function insCode(): string
    {
        if (preg_match('/^INS-\d+$/', $this->status) === 1) {
            return $this->status;
        }

        return $this->responseCode >= 200 && $this->responseCode < 300
            ? Response::SUCCESS
            : $this->insCodeForHttpStatus($this->responseCode);
    }

    protected function description(string $insCode): string
    {
        if ($this->status !== '' && preg_match('/^INS-\d+$/', $this->status) !== 1) {
            return $this->status;
        }

        return Response::message($insCode);
    }

    protected function insCodeForHttpStatus(int $httpCode): string
    {
        foreach (Response::$codes as $ins => $meta) {
            if (($meta['code'] ?? null) === $httpCode) {
                return $ins;
            }
        }

        return 'INS-1';
    }
}
