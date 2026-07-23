<?php

/**
 * Script manual de teste do pacote brilliant_mind/m-pesa.
 *
 * Uso:
 *   composer install        # uma vez, para instalar o vendor
 *   php teste.php
 *
 * Exercita o modo fake — não faz chamadas HTTP reais à API M-Pesa.
 */

declare(strict_types=1);

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Falta o vendor/. Corre `composer install` primeiro.\n");
    exit(1);
}
require $autoload;

use BrilliantMind\MPesa\MPesa;
use BrilliantMind\MPesa\Response;
use BrilliantMind\MPesa\Transaction;

$passed = 0;
$failed = 0;

function check(string $label, bool $condition, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [OK]   {$label}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$label}";
        if ($detail !== '') {
            echo " — {$detail}";
        }
        echo "\n";
    }
}

function section(string $title): void
{
    echo "\n== {$title} ==\n";
}

// ---------------------------------------------------------------------------
section('Response::describe()');

$ok = Response::describe('INS-0');
check('INS-0 devolve HTTP 200', $ok['code'] === 200, "got code={$ok['code']}");
check('INS-0 message correcta', str_contains($ok['message'], 'successfully'));

$err = Response::describe('INS-6');
check('INS-6 devolve HTTP 400', $err['code'] === 400);

$unknown = Response::describe('INS-XXX');
check('Código desconhecido não rebenta', $unknown['code'] === 0);
check('Código desconhecido tem mensagem', $unknown['message'] === 'Unknown response code');

$null = Response::describe(null);
check('Null não rebenta', $null['code'] === 0);

// ---------------------------------------------------------------------------
section('MPesa::fake() — sucesso (200)');

$mpesa = new MPesa();
$mpesa->fake(200);

$tx = $mpesa->c2b(150.00, '258858494607', 'TXREF1', 'PARTY1');
check('c2b devolve Transaction', $tx instanceof Transaction);
check('c2b responseCode = INS-0', $tx->getResponseCode() === 'INS-0');
check('c2b getStatusCode() = 200', $tx->getStatusCode() === 200);
check('c2b thirdPartyReference correcto', $tx->getThirdPartReference() === 'PARTY1');
check('c2b getMessage devolve string', is_string($tx->getMessage()) && $tx->getMessage() !== '');
check('c2b transactionID tem prefixo FAKE-', str_starts_with($tx->getTransactionID(), 'FAKE-'));

$tx = $mpesa->b2b(50.0, '258840000001', 'TXREF2', 'PARTY2');
check('b2b ok', $tx->getStatusCode() === 200);

$tx = $mpesa->b2c(25.0, '258840000002', 'TXREF3', 'PARTY3');
check('b2c ok', $tx->getStatusCode() === 200);

$tx = $mpesa->transaction('TXREF1', 'PARTY1');
check('transaction ok', $tx->getStatusCode() === 200);

$tx = $mpesa->reversal(150.0, 'TX-ABC123', 'PARTY1');
check('reversal ok', $tx->getStatusCode() === 200);

// ---------------------------------------------------------------------------
section('MPesa::fake() — erro (400) com status custom');

$mpesa = new MPesa();
$mpesa->fake(400, 'Saldo insuficiente');

$tx = $mpesa->c2b(9999999.0, '258840000000', 'TXREF-FAIL', 'PARTY-FAIL');
check('Erro: statusCode = 400', $tx->getStatusCode() === 400);
check('Erro: responseCode é INS-* de erro', str_starts_with($tx->getResponseCode(), 'INS-') && $tx->getResponseCode() !== 'INS-0');
check('Erro: mensagem custom preservada', $tx->getDescription() === 'Saldo insuficiente');

// ---------------------------------------------------------------------------
section('MPesa::setResponseCode() / setStatus()');

$mpesa = new MPesa();
$mpesa->fake();
$mpesa->setResponseCode(401);
$mpesa->setStatus('Token inválido');

$tx = $mpesa->c2b(10.0, '258840000000', 'TXREF-401', 'PARTY-401');
check('setResponseCode aplica-se', $tx->getStatusCode() === 401);
check('setStatus aplica-se', $tx->getDescription() === 'Token inválido');

// ---------------------------------------------------------------------------
section('Transaction — serialização');

$mpesa = new MPesa();
$mpesa->fake(200);
$tx = $mpesa->c2b(100.0, '258840000000', 'TXREF-SER', 'PARTY-SER');

$arr = $tx->toArray();
check('toArray() devolve array', is_array($arr));
check('toArray() contém responseCode', array_key_exists('responseCode', $arr));
check('toArray() contém transactionID', array_key_exists('transactionID', $arr));

$json = $tx->toJson();
check('toJson() devolve string JSON válida', is_string($json) && json_decode($json, true) !== null);

check('__get(responseCode) funciona', $tx->responseCode === 'INS-0');
check('__get(chave inexistente) devolve null', $tx->doesNotExist === null);

// ---------------------------------------------------------------------------
echo "\n";
echo str_repeat('-', 60) . "\n";
echo "Resultados: {$passed} passou(aram), {$failed} falhou(aram)\n";
echo str_repeat('-', 60) . "\n";

exit($failed === 0 ? 0 : 1);
