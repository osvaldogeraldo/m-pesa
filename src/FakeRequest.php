<?php

namespace BrilliantMind\MPesa;

use BrilliantMind\MPesa\Contracts\MPesaContract;

class FakeRequest implements MPesaContract
{
    public function __construct(
        protected int $responseCode = 200,
        protected string $status = ''
    ) {
    }

    public function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction($transactionReference, $thirdPartyReference);
    }

    public function b2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction($transactionReference, $thirdPartyReference);
    }

    public function b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction($transactionReference, $thirdPartyReference);
    }

    public function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction($transactionID, $thirdPartyReference);
    }

    public function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return $this->fakeTransaction($transactionReference, $thirdPartyReference);
    }

    protected function fakeTransaction(string $reference, string $thirdPartyReference): Transaction
    {
        $insCode = $this->responseCodeToIns($this->responseCode);
        $description = $this->status !== ''
            ? $this->status
            : Response::describe($insCode)['message'];

        return new Transaction([
            'output_ResponseCode'        => $insCode,
            'output_ResponseDesc'        => $description,
            'output_TransactionID'       => 'FAKE-' . strtoupper(bin2hex(random_bytes(5))),
            'output_ConversationID'      => 'FAKE-' . strtoupper(bin2hex(random_bytes(8))),
            'output_ThirdPartyReference' => $thirdPartyReference,
        ]);
    }

    protected function responseCodeToIns(int $httpCode): string
    {
        foreach (Response::$codes as $ins => $meta) {
            if (($meta['code'] ?? null) === $httpCode) {
                return $ins;
            }
        }

        return $httpCode >= 200 && $httpCode < 300 ? 'INS-0' : 'INS-1';
    }
}
