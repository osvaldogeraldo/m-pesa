<?php

namespace BrilliantMind\MPesa;

use BadMethodCallException;
use BrilliantMind\MPesa\Config\Config;
use BrilliantMind\MPesa\Contracts\FakeContract;
use BrilliantMind\MPesa\Contracts\MPesaContract;
use BrilliantMind\MPesa\Helpers\Parser;

/**
 * Entry point of the package.
 *
 * As operações existem em duas formas, ambas suportadas:
 *
 *   MPesa::c2b(...)            — estática, como na v1.x
 *   app('mpesa')->c2b(...)     — instância, via container ou facade
 *
 * Por isso os métodos de transacção são `protected` e despachados por
 * __call/__callStatic: um método público não-estático não pode ser chamado
 * estaticamente em PHP, enquanto um protegido passa pelos métodos mágicos
 * nos dois contextos.
 *
 * @method static Transaction c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction b2b(float $amount, string $receiverPartyCode, string $transactionReference, string $thirdPartyReference)
 * @method static Transaction transaction(string $transactionReference, string $thirdPartyReference)
 * @method static Transaction reversal(float $amount, string $transactionID, string $thirdPartyReference)
 * @method Transaction c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method Transaction b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference)
 * @method Transaction b2b(float $amount, string $receiverPartyCode, string $transactionReference, string $thirdPartyReference)
 * @method Transaction transaction(string $transactionReference, string $thirdPartyReference)
 * @method Transaction reversal(float $amount, string $transactionID, string $thirdPartyReference)
 */
class MPesa extends Config implements FakeContract
{
    /**
     * Operações despachadas pelos métodos mágicos.
     *
     * @var array<int, string>
     */
    protected const OPERATIONS = ['c2b', 'b2b', 'b2c', 'transaction', 'reversal'];

    /**
     * O estado de fake é estático de propósito: assim vale tanto para
     * MPesa::fake() como para app('mpesa')->fake().
     */
    protected static bool $fake = false;

    protected static string $fakeStatus = '';

    protected static int $fakeResponseCode = 200;

    /**
     * Liga o modo fake — nenhum pedido chega à rede.
     *
     * $status aceita um código INS ('INS-0', 'INS-2006') ou uma descrição livre.
     */
    public static function fake(int $responseCode = 200, string $status = ''): void
    {
        static::$fake = true;
        static::$fakeResponseCode = $responseCode;
        static::$fakeStatus = $status;

        FakeRequest::flush();
    }

    /**
     * Devolve exactamente este payload na próxima chamada.
     *
     * @param array<string, mixed> $payload
     */
    public static function fakeWith(array $payload, int $responseCode = 200): void
    {
        static::$fake = true;
        static::$fakeResponseCode = $responseCode;

        FakeRequest::queue($payload);
    }

    public static function setStatus(string $status): void
    {
        static::$fake = true;
        static::$fakeStatus = $status;
    }

    public static function setResponseCode(int $code): void
    {
        static::$fake = true;
        static::$fakeResponseCode = $code;
    }

    public static function stopFaking(): void
    {
        static::$fake = false;
        static::$fakeStatus = '';
        static::$fakeResponseCode = 200;

        FakeRequest::flush();
    }

    public static function isFaking(): bool
    {
        return static::$fake;
    }

    /**
     * Pedidos capturados durante o modo fake, para asserções em testes.
     *
     * @return array<int, array{operation: string, payload: array<string, mixed>}>
     */
    public static function recorded(): array
    {
        return FakeRequest::recorded();
    }

    /**
     * Constrói o cliente pronto a usar a partir da configuração actual.
     */
    public static function gateway(): MPesaContract
    {
        if (static::$fake) {
            return new FakeRequest(static::$fakeResponseCode, static::$fakeStatus);
        }

        static::ensureConfigured();

        return new Request(
            static::getHost(),
            static::getOrigin(),
            Parser::parse(static::getApiKey(), static::getPublicKey()),
            static::getServiceProviderCode(),
            static::getInitiatorIdentifier(),
            static::getSecurityCredential(),
            static::getPort(),
            [
                'ports' => static::getPorts(),
                'timeout' => static::getTimeout(),
                'connect_timeout' => static::getConnectTimeout(),
                'verify_ssl' => static::shouldVerifySsl(),
            ]
        );
    }

    /**
     * Aceita o número como as pessoas o escrevem e entrega à M-Pesa
     * o formato 258XXXXXXXXX que ela espera.
     */
    public static function normalizeMsisdn(string $msisdn): string
    {
        $digits = (string) preg_replace('/\D+/', '', $msisdn);

        // 00258... -> 258...
        if (str_starts_with($digits, '00258')) {
            $digits = substr($digits, 2);
        }

        // 0841234567 -> 841234567
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // 841234567 -> 258841234567
        if (strlen($digits) === 9 && str_starts_with($digits, '8')) {
            $digits = '258' . $digits;
        }

        return $digits;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->dispatch($method, $arguments);
    }

    public static function __callStatic(string $method, array $arguments)
    {
        return (new static())->dispatch($method, $arguments);
    }

    /**
     * Cliente paga à empresa. A M-Pesa envia um USSD Push para confirmar o PIN.
     */
    protected function c2b(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->c2b($amount, static::normalizeMsisdn($msisdn), $transactionReference, $thirdPartyReference);
    }

    /**
     * Empresa paga a outra empresa. $receiverPartyCode é o shortcode de quem recebe.
     */
    protected function b2b(float $amount, string $receiverPartyCode, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->b2b($amount, $receiverPartyCode, $transactionReference, $thirdPartyReference);
    }

    /**
     * Empresa paga ao cliente.
     */
    protected function b2c(float $amount, string $msisdn, string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->b2c($amount, static::normalizeMsisdn($msisdn), $transactionReference, $thirdPartyReference);
    }

    /**
     * Consulta o estado actual de uma transacção.
     */
    protected function transaction(string $transactionReference, string $thirdPartyReference): Transaction
    {
        return static::gateway()->transaction($transactionReference, $thirdPartyReference);
    }

    /**
     * Reverte uma transacção bem sucedida.
     */
    protected function reversal(float $amount, string $transactionID, string $thirdPartyReference): Transaction
    {
        return static::gateway()->reversal($amount, $transactionID, $thirdPartyReference);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    protected function dispatch(string $method, array $arguments): Transaction
    {
        if (! in_array($method, static::OPERATIONS, true)) {
            throw new BadMethodCallException(
                sprintf('Method %s::%s() does not exist.', static::class, $method)
            );
        }

        return $this->{$method}(...$arguments);
    }
}
