# M-Pesa PHP SDK (Laravel)

SDK para interagir com a API M-Pesa Moçambique: C2B, B2B, B2C, consulta de transação e reversão.

## Índice

- [Instalação](#instalação)
- [Configuração](#configuração)
  - [Variáveis de ambiente](#variáveis-de-ambiente)
  - [Tabela completa de variáveis](#tabela-completa-de-variáveis)
  - [Publicar o ficheiro de configuração](#publicar-o-ficheiro-de-configuração-opcional)
- [Utilização](#utilização)
  - [C2B, B2B, B2C](#c2b--cliente-para-empresa)
  - [Consulta de transação](#consulta-de-transação)
  - [Reversão](#reversão)
- [O objecto Transaction](#o-objecto-transaction)
- [Códigos de resposta](#códigos-de-resposta)
- [Modo fake (testes)](#modo-fake-testes)
- [Requisitos](#requisitos)
- [Notas sobre portos M-Pesa](#notas-sobre-portos-m-pesa)

## Instalação

```shell
composer require brilliant_mind/m-pesa
```

O pacote usa _package auto-discovery_ do Laravel — o `ServiceProvider` e o alias `Mpesa` são registados automaticamente.

## Configuração

### Variáveis de ambiente

#### Desenvolvimento (sandbox)

```dotenv
# MPESA DEVELOPMENT
MPESA_API_KEY=sua_api_key_sandbox
MPESA_PUBLIC_KEY=sua_public_key_sandbox
MPESA_ENVIRONMENT=development
MPESA_SERVICE_PROVIDER_CODE=171717
MPESA_ORIGIN=developer.mpesa.vm.co.mz
MPESA_INITIATOR_IDENTIFIER=
MPESA_SECURITY_CREDENTIAL=
```

#### Produção

```dotenv
# MPESA PRODUCTION
MPESA_API_KEY=sua_api_key_producao
MPESA_PUBLIC_KEY=sua_public_key_producao
MPESA_ENVIRONMENT=production
MPESA_SERVICE_PROVIDER_CODE=903153
MPESA_ORIGIN=developer.mpesa.vm.co.mz
MPESA_INITIATOR_IDENTIFIER=seu_initiator
MPESA_SECURITY_CREDENTIAL=seu_credential_encriptado
```

> **Nota:** `MPESA_ENV` continua a ser aceite como alias retrocompatível de `MPESA_ENVIRONMENT`.

### Tabela completa de variáveis

| Variável | Obrigatória | Default | Descrição |
|---|---|---|---|
| `MPESA_API_KEY` | **Sim** | `''` | Chave de API fornecida pelo portal da M-Pesa (sandbox ou produção). |
| `MPESA_PUBLIC_KEY` | **Sim** | `''` | Chave pública RSA (base64, sem cabeçalhos `-----BEGIN...`). Usada para encriptar o `MPESA_API_KEY` em cada chamada. |
| `MPESA_ENVIRONMENT` | Não | `development` | `development` ou `production`. Determina o host por defeito (sandbox vs prod). |
| `MPESA_SERVICE_PROVIDER_CODE` | Não | `171717` | Código do _service provider_. `171717` é o valor de sandbox; em produção usa o teu código (ex. `903153`). |
| `MPESA_HOST` | Não | derivado | Override manual do host. Por defeito a SDK escolhe `api.sandbox.vm.co.mz` (development) ou `api.vm.co.mz` (production). Só preenche se quiseres forçar outro endpoint. |
| `MPESA_ORIGIN` | Não | `developer.mpesa.vm.co.mz` | Valor do header `Origin` enviado a cada pedido. |
| `MPESA_INITIATOR_IDENTIFIER` | Apenas reversão | `''` | Identificador do _initiator_ — obrigatório para `reversal()`. |
| `MPESA_SECURITY_CREDENTIAL` | Apenas reversão | `''` | Credencial encriptada do _initiator_ — obrigatória para `reversal()`. |

> **`MPESA_PORT` não existe nesta SDK.** Os portos M-Pesa são por operação (`18352` C2B, `18345` B2C, `18349` B2B, `18353` query, `18354` reversal) e são geridos internamente. Podes ter `MPESA_PORT` no teu `.env` — será simplesmente ignorado.

### Publicar o ficheiro de configuração (opcional)

Só é necessário se quiseres alterar defaults além do `.env`:

```shell
php artisan vendor:publish --tag=mpesa-config
```

Isto cria `config/mpesa.php`.

### Configuração em runtime (multi-tenant)

Quando as credenciais não vêm do `.env` (ex. são dinâmicas por tenant), podes chamar `Mpesa::config(...)` antes de cada operação:

```php
use Mpesa;

Mpesa::config(
    $apiKey,
    $publicKey,
    // $environment            = null,  // 'development' | 'production'
    // $serviceProviderCode    = null,  // ex. '903153'
    // $origin                 = null,  // 'developer.mpesa.vm.co.mz'
    // $initiatorIdentifier    = null,  // só para reversal()
    // $securityCredential     = null,  // só para reversal()
    // $host                   = null,  // override do host (raro)
);

$tx = Mpesa::c2b($amount, $msisdn, $ref, $thirdPartyRef);
```

Quaisquer argumentos passados aqui sobrepõem-se aos do `.env` para o ciclo de vida do request actual. Args a `null` mantêm o valor previamente configurado.

> **Não existe parâmetro `port`.** Os portos M-Pesa são por operação (ver [Notas sobre portos M-Pesa](#notas-sobre-portos-m-pesa)) e estão hardcoded na SDK. Se vires `config('services.mpesa.port')` em código antigo, pode ser removido.

## Utilização

Basta usar o facade `Mpesa` em qualquer lado (controllers, jobs, services):

```php
use Mpesa;

$transactionReference = bin2hex(random_bytes(6));
$thirdPartyReference  = bin2hex(random_bytes(6));

$response = Mpesa::c2b(1, '258846000000', $transactionReference, $thirdPartyReference);

return $response->toArray();
```

### C2B — Cliente para Empresa

```php
$response = Mpesa::c2b($amount, $msisdn, $transactionReference, $thirdPartyReference);
```

| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$amount` | `float` | Valor a debitar do cliente. |
| `$msisdn` | `string` | MSISDN do cliente, formato internacional (ex. `258846000000`). |
| `$transactionReference` | `string` | Referência interna da transação (1–20 caracteres). |
| `$thirdPartyReference` | `string` | Referência única do lado do _third party_ (idempotência). |

### B2C — Empresa para Cliente

```php
$response = Mpesa::b2c($amount, $msisdn, $transactionReference, $thirdPartyReference);
```

### B2B — Empresa para Empresa

```php
$response = Mpesa::b2b($amount, $msisdn, $transactionReference, $thirdPartyReference);
```

### Consulta de transação

```php
$response = Mpesa::transaction($transactionReference, $thirdPartyReference);
```

| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$transactionReference` | `string` | A referência (`input_QueryReference`) da transação a consultar. |
| `$thirdPartyReference` | `string` | Mesma `thirdPartyReference` usada na transação original. |

### Reversão

```php
$response = Mpesa::reversal($amount, $transactionID, $thirdPartyReference);
```

| Parâmetro | Tipo | Descrição |
|---|---|---|
| `$amount` | `float` | Valor a reverter. |
| `$transactionID` | `string` | ID interno M-Pesa da transação original (`output_TransactionID`). |
| `$thirdPartyReference` | `string` | Referência única do lado do _third party_. |

> Exige `MPESA_INITIATOR_IDENTIFIER` e `MPESA_SECURITY_CREDENTIAL` configurados.

## O objecto `Transaction`

Todos os métodos devolvem uma instância de `BrilliantMind\MPesa\Transaction`. Métodos disponíveis:

```php
$tx->getResponseCode();        // 'INS-0', 'INS-6', ...
$tx->getStatusCode();          // int — equivalente HTTP (200, 400, 500, ...)
$tx->getMessage();             // descrição associada ao código INS-*
$tx->getDescription();         // descrição vinda da API M-Pesa (output_ResponseDesc)
$tx->getTransactionID();       // ID interno M-Pesa
$tx->getConversationID();      // ID de conversação
$tx->getThirdPartReference();  // o teu thirdPartyReference

$tx->toArray();                // array normalizado
$tx->toJson();                 // string JSON
$tx->jsonSerialize();          // suporte a json_encode()

$tx->responseCode;             // acesso mágico aos campos do toArray()
```

Exemplo de tratamento:

```php
$tx = Mpesa::c2b(100.0, '258846000000', $ref, $thirdPartyRef);

if ($tx->getResponseCode() === 'INS-0') {
    // sucesso — guarda $tx->getTransactionID() em DB
} else {
    Log::warning('M-Pesa falhou', $tx->toArray());
}
```

## Códigos de resposta

A SDK conhece 33 códigos `INS-*` documentados pela M-Pesa, com o equivalente HTTP e descrição. Lista parcial:

| Código | HTTP | Significado |
|---|---|---|
| `INS-0` | 200 | Request processed successfully |
| `INS-1` | 500 | Internal Error |
| `INS-2` | 401 | Invalid API Key |
| `INS-5` | 400 | Transaction cancelled by customer |
| `INS-6` | 400 | Transaction Failed |
| `INS-9` | 408 | Request timeout |
| `INS-10` | 400 | Duplicate Transaction |
| `INS-13` | 400 | Invalid Shortcode Used |
| `INS-15` | 400 | Invalid Amount Used |
| `INS-17` | 400 | Invalid Transaction Reference (length 1–20) |
| `INS-20` | 400 | Not All Parameters Provided |
| `INS-21` | 400 | Parameter validations failed |
| `INS-26` | 401 | Not authorised |
| `INS-996` | 400 | Customer Account Status Not Active |
| `INS-2006` | 400 | Insufficient balance |
| `INS-2051` | 400 | Invalid MSISDN |
| `INS-2057` | 400 | MSISDN is not registered |

Lista completa em [src/Response.php](src/Response.php). Para códigos desconhecidos, `Response::describe()` devolve `['code' => 0, 'message' => 'Unknown response code']` em vez de rebentar.

## Modo fake (testes)

Útil para testes unitários — não faz chamadas HTTP, devolve `Transaction` canónicas:

```php
use BrilliantMind\MPesa\MPesa;

$mpesa = app('mpesa');               // ou: resolve(MPesa::class)
$mpesa->fake(200);                   // sucesso (INS-0)

$tx = $mpesa->c2b(100.0, '258840000000', 'REF', 'PARTY');
// $tx->getStatusCode() === 200
// $tx->getResponseCode() === 'INS-0'
// $tx->getTransactionID() começa por 'FAKE-'
```

Cenários de erro:

```php
$mpesa->fake(400, 'Saldo insuficiente');
$tx = $mpesa->c2b(...);
// $tx->getStatusCode() === 400
// $tx->getDescription() === 'Saldo insuficiente'
```

Ajustar a meio da sessão:

```php
$mpesa->setResponseCode(401);
$mpesa->setStatus('Token inválido');
```

O facade `Mpesa::fake(...)` também funciona porque a facade proxia métodos ao singleton.

Ver [teste.php](teste.php) na raiz para um runner manual end-to-end.

## Requisitos

- PHP `^8.1`
- Laravel 10, 11 ou 12
- Extensões: `openssl`, `json`, `curl`

## Notas sobre portos M-Pesa

A API M-Pesa Moçambique usa portos diferentes por operação:

| Operação | Porto | Endpoint |
|---|---|---|
| C2B | `18352` | `/ipg/v1x/c2bPayment/singleStage/` |
| B2C | `18345` | `/ipg/v1x/b2cPayment/` |
| B2B | `18349` | `/ipg/v1x/b2bPayment/` |
| Query | `18353` | `/ipg/v1x/queryTransactionStatus/` |
| Reversal | `18354` | `/ipg/v1x/reversal/` |

A SDK trata desta dispersão internamente — não é preciso configurar portos no `.env`.
