# M-Pesa PHP SDK (Laravel)

SDK para interagir com a API M-Pesa Moçambique: C2B, B2B, B2C, consulta de transação e reversão.

Feito para funcionar no Laravel **sem configuração manual**: instala, coloca duas variáveis no `.env` e já podes cobrar.

## Índice

- [Instalação](#instalação)
- [Configuração](#configuração)
  - [Variáveis de ambiente](#variáveis-de-ambiente)
  - [Tabela completa de variáveis](#tabela-completa-de-variáveis)
  - [Publicar o ficheiro de configuração](#publicar-o-ficheiro-de-configuração-opcional)
- [Utilização](#utilização)
  - [C2B — cliente paga à empresa](#c2b--cliente-paga-à-empresa)
  - [B2C — empresa paga ao cliente](#b2c--empresa-paga-ao-cliente)
  - [B2B — empresa paga a outra empresa](#b2b--empresa-paga-a-outra-empresa)
  - [Consulta de transação](#consulta-de-transação)
  - [Reversão](#reversão)
- [O objecto Transaction](#o-objecto-transaction)
- [Códigos de resposta](#códigos-de-resposta)
- [Modo fake (testes)](#modo-fake-testes)
- [Erros](#erros)
- [Fora do Laravel](#fora-do-laravel)
- [Notas sobre portos M-Pesa](#notas-sobre-portos-m-pesa)
- [Requisitos](#requisitos)

## Instalação

```shell
composer require brilliant_mind/m-pesa
```

É tudo. O pacote usa _package auto-discovery_ do Laravel — o `ServiceProvider` e os aliases `MPesa` e `Mpesa` são registados automaticamente. **Não precisas** de publicar o config, nem de editar `config/app.php`, `bootstrap/providers.php` ou o `AppServiceProvider`.

## Configuração

### Variáveis de ambiente

Só `MPESA_API_KEY` e `MPESA_PUBLIC_KEY` são obrigatórias. Tudo o resto tem valores por omissão prontos para o sandbox.

#### Desenvolvimento (sandbox)

```dotenv
# MPESA DEVELOPMENT
MPESA_API_KEY=sua_api_key_sandbox
MPESA_PUBLIC_KEY="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB...FwIDAQAB"
MPESA_ENVIRONMENT=development
MPESA_SERVICE_PROVIDER_CODE=171717
```

#### Produção

```dotenv
# MPESA PRODUCTION
MPESA_API_KEY=sua_api_key_producao
MPESA_PUBLIC_KEY="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB...FwIDAQAB"
MPESA_ENVIRONMENT=production
MPESA_SERVICE_PROVIDER_CODE=903153
MPESA_INITIATOR_IDENTIFIER=seu_initiator
MPESA_SECURITY_CREDENTIAL=seu_credential_encriptado
```

> **A chave pública** é uma linha longa de base64 — envolve-a em aspas duplas.
> É aceite em qualquer formato: uma linha, com quebras de linha, ou o bloco PEM completo.
> Se preferires guardá-la num ficheiro, deixa `MPESA_PUBLIC_KEY` vazia e usa `MPESA_PUBLIC_KEY_PATH`.

> **Nota:** `MPESA_ENV` continua a ser aceite como alias retrocompatível de `MPESA_ENVIRONMENT`.

Se mudaste o `.env` e nada aconteceu, limpa a cache: `php artisan config:clear`.

### Tabela completa de variáveis

| Variável | Obrigatória | Default | Descrição |
|---|---|---|---|
| `MPESA_API_KEY` | **Sim** | `''` | Chave de API fornecida pelo portal da M-Pesa (sandbox ou produção). |
| `MPESA_PUBLIC_KEY` | **Sim** | `''` | Chave pública RSA em base64. Usada para encriptar o `MPESA_API_KEY` em cada chamada. |
| `MPESA_PUBLIC_KEY_PATH` | Não | `''` | Caminho para um ficheiro com a chave pública. Usado apenas se `MPESA_PUBLIC_KEY` estiver vazia. |
| `MPESA_ENVIRONMENT` | Não | `development` | `development` (sandbox) ou `production`. Determina o host por defeito. Aceita também `sandbox`, `dev`, `prod` e `live`. |
| `MPESA_SERVICE_PROVIDER_CODE` | Não | `171717` | Código do _service provider_. `171717` é o de sandbox; em produção usa o teu (ex. `903153`). |
| `MPESA_ORIGIN` | Não | `developer.mpesa.vm.co.mz` | Valor do header `Origin` enviado a cada pedido. |
| `MPESA_INITIATOR_IDENTIFIER` | Apenas reversão | `''` | Identificador do _initiator_ — obrigatório para `reversal()`. |
| `MPESA_SECURITY_CREDENTIAL` | Apenas reversão | `''` | Credencial encriptada do _initiator_ — obrigatória para `reversal()`. |
| `MPESA_HOST` | Não | derivado | Override manual do host. Por defeito escolhe `api.sandbox.vm.co.mz` ou `api.vm.co.mz`. |
| `MPESA_PORT` | Não | `''` | Força um único porto para todas as operações. Vazio usa o porto de cada operação. |
| `MPESA_TIMEOUT` | Não | `90` | Timeout de resposta em segundos. O C2B espera o PIN do cliente, por isso é longo. |
| `MPESA_CONNECT_TIMEOUT` | Não | `30` | Timeout de ligação em segundos. |
| `MPESA_VERIFY_SSL` | Não | `true` | Verificação do certificado TLS. |

### Publicar o ficheiro de configuração (opcional)

Só é necessário se quiseres alterar defaults além do `.env`:

```shell
php artisan vendor:publish --tag=mpesa-config
```

## Utilização

As operações existem em duas formas, ambas suportadas:

```php
// Estática — como na v1.x
use BrilliantMind\MPesa\MPesa;

$transaction = MPesa::c2b(100, '841234567', 'ENC0001', 'TPR0001');

// De instância — via container, injecção de dependências ou facade
$transaction = app('mpesa')->c2b(100, '841234567', 'ENC0001', 'TPR0001');
```

A facade está disponível globalmente, sem `use`, em qualquer das grafias (`MPesa` ou `Mpesa`):

```php
$transaction = MPesa::c2b(
    amount: 100,
    msisdn: '841234567',
    transactionReference: 'ENC0001',
    thirdPartyReference: 'TPR0001',
);

if ($transaction->isSuccessful()) {
    // guardar $transaction->getTransactionID()
}

return $transaction->getMessage();
```

O número de telemóvel é normalizado automaticamente — `841234567`, `0841234567`, `+258 84 123 4567` e `258841234567` produzem todos `258841234567`.

### C2B — cliente paga à empresa

Debita a carteira do cliente e credita a da empresa. A M-Pesa envia um USSD Push ao cliente para confirmar o PIN.

```php
$transaction = MPesa::c2b(100, '841234567', 'ENC0001', 'TPR0001');
```

### B2C — empresa paga ao cliente

```php
$transaction = MPesa::b2c(100, '841234567', 'ENC0002', 'TPR0002');
```

### B2B — empresa paga a outra empresa

O segundo argumento é o **shortcode da empresa que recebe**, não um número de telemóvel.

```php
$transaction = MPesa::b2b(100, '979797', 'ENC0003', 'TPR0003');
```

### Consulta de transação

```php
$transaction = MPesa::transaction('ENC0001', 'TPR0001');

$transaction->getTransactionStatus(); // ex.: 'Completed'
```

### Reversão

Requer `MPESA_INITIATOR_IDENTIFIER` e `MPESA_SECURITY_CREDENTIAL`.

```php
$transaction = MPesa::reversal(100, '49XCDF6', 'TPR0001');
```

## O objecto Transaction

```php
$transaction->isSuccessful();          // bool — true quando o código é INS-0
$transaction->failed();                // bool
$transaction->getResponseCode();       // 'INS-0'
$transaction->getMessage();            // descrição legível
$transaction->getTransactionID();      // id da transacção na M-Pesa
$transaction->getConversationID();
$transaction->getThirdPartReference();
$transaction->getTransactionStatus();  // só na consulta de estado
$transaction->getHttpStatus();         // HTTP devolvido pelo gateway
$transaction->getStatusCode();         // HTTP equivalente ao código INS
$transaction->raw();                   // payload original da M-Pesa
$transaction->toArray();
$transaction->toJson();
```

Nunca lança excepção por a M-Pesa ter recusado a transacção: uma recusa é sempre um `Transaction` com `failed() === true`. Excepções ficam reservadas para erros de configuração e de rede (ver [Erros](#erros)).

## Códigos de resposta

A tabela completa de códigos INS está em `BrilliantMind\MPesa\Response::$codes`:

```php
use BrilliantMind\MPesa\Response;

Response::message('INS-2006');   // 'Insufficient balance'
Response::status('INS-2006');    // 422
Response::describe('INS-2006');  // ['code' => 422, 'message' => 'Insufficient balance']
Response::isSuccessful('INS-0'); // true
```

## Modo fake (testes)

Nos teus testes usa `fake()` para não tocar na rede:

```php
use BrilliantMind\MPesa\MPesa;

MPesa::fake();                       // devolve sempre INS-0
MPesa::fake(422, 'INS-2006');        // simula saldo insuficiente
MPesa::fakeWith([                    // controlo total do payload
    'output_ResponseCode' => 'INS-0',
    'output_TransactionID' => '49XCDF6',
]);

MPesa::c2b(100, '841234567', 'REF', 'TPR');

MPesa::recorded();  // pedidos capturados, para asserções
MPesa::stopFaking();
```

## Erros

| Excepção | Quando acontece | Como resolver |
| --- | --- | --- |
| `MissingConfigurationException` | `MPESA_API_KEY` ou `MPESA_PUBLIC_KEY` em falta | Preenche o `.env` e corre `php artisan config:clear` |
| `EncryptionException` | A chave pública não é válida | Copia outra vez a _Public Key_ do portal da M-Pesa |
| `InvalidEnvironmentException` | `MPESA_ENVIRONMENT` com valor desconhecido | Usa `development` ou `production` |
| `ConnectionException` | O gateway não respondeu | Confirma que o teu servidor permite tráfego de saída nos portos M-Pesa (ver abaixo) |

Todas herdam de `BrilliantMind\MPesa\Exceptions\MPesaException`, por isso podes apanhar tudo de uma vez:

```php
use BrilliantMind\MPesa\Exceptions\MPesaException;

try {
    $transaction = MPesa::c2b(100, $msisdn, $reference, $thirdPartyReference);
} catch (MPesaException $e) {
    report($e);
}
```

## Fora do Laravel

O pacote também funciona em PHP puro; basta configurá-lo à mão:

```php
require __DIR__ . '/vendor/autoload.php';

use BrilliantMind\MPesa\MPesa;

MPesa::config(
    api_key: 'a-tua-api-key',
    public_key: 'a-tua-public-key',
    environment: 'development',
    service_provider_code: '171717',
);

$transaction = MPesa::c2b(100, '841234567', 'ENC0001', 'TPR0001');
```

`MPesa::config()` aceita argumentos parciais — o que não passares mantém o valor actual.

## Notas sobre portos M-Pesa

O gateway escuta num porto diferente por operação e a SDK trata disso sozinha:

| Operação | Porto |
|---|---|
| C2B | `18352` |
| B2C | `18345` |
| B2B | `18349` |
| Consulta de estado | `18353` |
| Reversão | `18354` |

Como não são portos padrão, muitos alojamentos partilhados bloqueiam o tráfego de saída para eles — é a causa mais comum de `ConnectionException`. Se precisares de forçar um porto único (proxy, por exemplo), define `MPESA_PORT`.

## Requisitos

- PHP `^8.1`
- Extensões `openssl`, `json`, `curl`
- Laravel 10, 11 ou 12 (opcional)
