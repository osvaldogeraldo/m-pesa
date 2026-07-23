<?php

namespace BrilliantMind\MPesa\Tests\Unit;

use BrilliantMind\MPesa\Tests\TestCase;
use BrilliantMind\MPesa\Transaction;

class TransactionTest extends TestCase
{
    public function test_it_exposes_the_normalized_payload(): void
    {
        $transaction = new Transaction([
            'output_ResponseCode' => 'INS-0',
            'output_ResponseDesc' => 'Request processed successfully',
            'output_TransactionID' => '49XCDF6',
            'output_ConversationID' => 'aaa-bbb',
            'output_ThirdPartyReference' => 'TPR1',
        ]);

        $this->assertTrue($transaction->isSuccessful());
        $this->assertSame('49XCDF6', $transaction->getTransactionID());
        $this->assertSame('aaa-bbb', $transaction->getConversationID());
        $this->assertSame('TPR1', $transaction->getThirdPartReference());
        $this->assertSame('Request processed successfully', $transaction->getMessage());
        $this->assertSame('49XCDF6', $transaction->transactionID);
    }

    /**
     * Regression: the typed getters used to raise a TypeError whenever M-Pesa
     * answered with a partial payload.
     */
    public function test_an_empty_payload_never_fatals(): void
    {
        $transaction = new Transaction([]);

        $this->assertSame('', $transaction->getResponseCode());
        $this->assertSame('', $transaction->getTransactionID());
        $this->assertSame('', $transaction->getConversationID());
        $this->assertSame('', $transaction->getThirdPartReference());
        $this->assertFalse($transaction->isSuccessful());
        $this->assertSame('Unknown response from the M-Pesa gateway', $transaction->getMessage());
        $this->assertNull($transaction->transactionID);
    }

    public function test_it_falls_back_to_the_documented_message_for_a_known_code(): void
    {
        $transaction = new Transaction(['output_ResponseCode' => 'INS-2006']);

        $this->assertSame('Insufficient balance', $transaction->getMessage());
    }

    public function test_it_reads_the_status_returned_by_the_query_endpoint(): void
    {
        $transaction = new Transaction([
            'output_ResponseCode' => 'INS-0',
            'output_ResponseTransactionStatus' => 'Completed',
        ]);

        $this->assertSame('Completed', $transaction->getTransactionStatus());
        $this->assertSame('Completed', $transaction->toArray()['transactionStatus']);
    }

    public function test_it_serializes_to_json(): void
    {
        $transaction = new Transaction(['output_ResponseCode' => 'INS-0', 'output_TransactionID' => 'X1']);

        $decoded = json_decode($transaction->toJson(), true);

        $this->assertTrue($decoded['successful']);
        $this->assertSame('X1', $decoded['transactionID']);
    }
}
