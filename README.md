# M-Pesa PHP SDK

Pacote para interagir com a API do M-Pesa Moçambique: C2B, B2B, B2C, consulta de estado e reversões.

Feito para funcionar no Laravel **sem configuração manual**: instala, coloca duas variáveis no `.env` e já podes cobrar.

---

## Instalação

```shell
composer require brilliant_mind/m-pesa
```

É tudo. O *service provider* e a facade `MPesa` são registados automaticamente pelo package discovery do Laravel. **Não precisas** de publicar o config, nem de editar `config/app.php`, `bootstrap/providers.php` ou o `AppServiceProvider`.

## Configuração

Coloca no `.env`:

```dotenv
MPESA_API_KEY="a-tua-api-key"
MPESA_PUBLIC_KEY="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB...FwIDAQAB"
```

Só isso é obrigatório. Tudo o resto tem valores por omissão prontos para o sandbox:

| Variável | Por omissão | Para que serve |
| --- | --- | --- |
| `MPESA_ENVIRONMENT` | `development` | `development` (sandbox) ou `production` |
| `MPESA_SERVICE_PROVIDER_CODE` | `171717` | O teu shortcode. Muda antes de ir para produção |
| `MPESA_ORIGIN` | `developer.mpesa.vm.co.mz` | Header `Origin` |
| `MPESA_INITIATOR_IDENTIFIER` | *(vazio)* | Só necessário para reversões |
| `MPESA_SECURITY_CREDENTIAL` | *(vazio)* | Só necessário para reversões |
| `MPESA_TIMEOUT` | `90` | O C2B espera o PIN do cliente, por isso é longo |
| `MPESA_CONNECT_TIMEOUT` | `30` | Timeout de ligação |
| `MPESA_VERIFY_SSL` | `true` | Verificação do certificado TLS |
| `MPESA_HOST` / `MPESA_PORT` | *(vazio)* | Só para forçar um host/porta (proxy) |

> **A chave pública** é uma linha longa de base64. Envolve-a em aspas duplas no `.env`.
> Se preferires guardá-la num ficheiro, deixa `MPESA_PUBLIC_KEY` vazia e usa
> `MPESA_PUBLIC_KEY_PATH=/caminho/para/public_key.txt`.
> A chave é aceite em qualquer formato: uma linha, com quebras de linha, ou o bloco PEM completo.

Publicar o ficheiro de config é **opcional**, só se quiseres alterar defaults:

```shell
php artisan vendor:publish --tag=mpesa-config
```

Se mudaste o `.env` e nada aconteceu, limpa a cache: `php artisan config:clear`.

## Utilização

A facade `MPesa` está disponível globalmente, sem `use`:

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

Ou com a classe directamente:

```php
use BrilliantMind\MPesa\MPesa;

$transaction = MPesa::c2b(100, '841234567', 'ENC0001', 'TPR0001');
```

O número de telemóvel é normalizado automaticamente — `841234567`, `0841234567`,
`+258 84 123 4567` e `258841234567` produzem todos `258841234567`.

### O objecto Transaction

```php
$transaction->isSuccessful();          // bool — true quando o código é INS-0
$transaction->failed();                // bool
$transaction->getResponseCode();       // 'INS-0'
$transaction->getMessage();            // descrição legível
$transaction->getTransactionID();      // id da transacção na M-Pesa
$transaction->getConversationID();
$transaction->getThirdPartReference();
$transaction->getTransactionStatus();  // só na consulta de estado
$transaction->getHttpStatus();         // int
$transaction->raw();                   // payload original da M-Pesa
$transaction->toArray();
$transaction->toJson();
```

Nunca lança excepção por a M-Pesa ter recusado a transacção: uma recusa é sempre um
`Transaction` com `failed() === true`. Excepções ficam reservadas para erros de
configuração e de rede (ver [Erros](#erros)).

### C2B — cliente paga à empresa

Debita a carteira do cliente e credita a da empresa. A M-Pesa envia um USSD Push
ao cliente para confirmar o PIN.

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

### Consultar o estado de uma transacção

```php
$transaction = MPesa::transaction('ENC0001', 'TPR0001');

$transaction->getTransactionStatus(); // ex.: 'Completed'
```

### Reversão

Requer `MPESA_INITIATOR_IDENTIFIER` e `MPESA_SECURITY_CREDENTIAL`.

```php
$transaction = MPesa::reversal(100, '49XCDF6', 'TPR0001');
```

## Testes

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
| `EncryptionException` | A chave pública não é válida | Copia outra vez a *Public Key* do portal da M-Pesa |
| `InvalidEnvironmentException` | `MPESA_ENVIRONMENT` com valor desconhecido | Usa `development` ou `production` |
| `ConnectionException` | O gateway não respondeu | O M-Pesa usa as portas 18345, 18349, 18352, 18353 e 18354 — confirma que o teu servidor permite tráfego de saída nessas portas |

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

## Requisitos

- PHP `^8.1`
- Extensões `openssl`, `json`, `curl`
- Laravel 10, 11 ou 12 (opcional)
