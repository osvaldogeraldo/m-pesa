<?php

namespace BrilliantMind\MPesa\Tests\Feature;

use BrilliantMind\MPesa\Exceptions\MissingConfigurationException;
use BrilliantMind\MPesa\Facades\MPesa as MPesaFacade;
use BrilliantMind\MPesa\MPesa;
use BrilliantMind\MPesa\Tests\TestCase;
use BrilliantMind\MPesa\Transaction;

class TransactionsTest extends TestCase
{
    protected function configureCredentials(): void
    {
        config([
            'mpesa.api_key' => 'api-key',
            'mpesa.public_key' => self::samplePublicKey(),
        ]);
    }

    public function test_a_c2b_call_only_needs_the_env_credentials(): void
    {
        $this->configureCredentials();
        MPesa::fake();

        $transaction = MPesa::c2b(10.0, '841234567', 'REF123', 'TPR123');

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->isSuccessful());
        $this->assertSame('INS-0', $transaction->getResponseCode());

        [$request] = MPesa::recorded();
        $this->assertSame('c2b', $request['operation']);
        $this->assertSame('258841234567', $request['payload']['input_CustomerMSISDN']);
        $this->assertSame('171717', $request['payload']['input_ServiceProviderCode']);
    }

    public function test_it_works_through_the_facade(): void
    {
        $this->configureCredentials();
        MPesaFacade::fake();

        $this->assertTrue(MPesaFacade::c2b(10.0, '841234567', 'REF', 'TPR')->isSuccessful());
    }

    /**
     * The alias composer discovery registers, so \MPesa works with no imports.
     */
    public function test_it_works_through_the_global_alias(): void
    {
        $this->configureCredentials();
        \MPesa::fake();

        $this->assertTrue(\MPesa::c2b(10.0, '841234567', 'REF', 'TPR')->isSuccessful());
    }

    public function test_it_reports_a_failed_transaction_instead_of_throwing(): void
    {
        $this->configureCredentials();
        MPesa::fake(422, 'INS-5');

        $transaction = MPesa::c2b(10.0, '841234567', 'REF', 'TPR');

        $this->assertFalse($transaction->isSuccessful());
        $this->assertTrue($transaction->failed());
        $this->assertSame('INS-5', $transaction->getResponseCode());
        $this->assertSame('Transaction cancelled by customer', $transaction->getMessage());
        $this->assertSame(422, $transaction->getHttpStatus());
    }

    public function test_each_operation_hits_its_own_endpoint(): void
    {
        $this->configureCredentials();
        MPesa::fake();

        MPesa::c2b(10.0, '841234567', 'REF', 'TPR');
        MPesa::b2c(10.0, '841234567', 'REF', 'TPR');
        MPesa::b2b(10.0, '979797', 'REF', 'TPR');
        MPesa::transaction('REF', 'TPR');
        MPesa::reversal(10.0, 'TXN', 'TPR');

        $this->assertSame(
            ['c2b', 'b2c', 'b2b', 'status', 'reversal'],
            array_column(MPesa::recorded(), 'operation')
        );
    }

    public function test_it_throws_a_readable_error_when_the_credentials_are_missing(): void
    {
        config(['mpesa.api_key' => '', 'mpesa.public_key' => '']);

        $this->expectException(MissingConfigurationException::class);
        $this->expectExceptionMessage('MPESA_API_KEY');

        MPesa::c2b(10.0, '841234567', 'REF', 'TPR');
    }
}
