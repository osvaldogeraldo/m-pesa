# M-Pesa PHP SDK

Este pacote permite interagir com a API do M-Pesa, facilitando transações como C2B, B2B, B2C, e consultas de transações. Ele também oferece suporte para transações de reversão.

## Installation

To install this dependency, just run the command below:
```shell
composer require BrilliantMind/m-pesa
```

## Configuração

Antes de utilizar o SDK, você deve configurar as credenciais e parâmetros da API do M-Pesa. O pacote permite que você defina as seguintes variáveis:

- **`api_key`** — Sua chave de API fornecida pela M-Pesa.
- **`public_key`** — Chave pública utilizada para encriptação das requisições.
- **`environment`** — O ambiente de execução da API, que pode ser:
  - `'development'` para o ambiente de desenvolvimento (sandbox).
  - `'production'` para o ambiente de produção.
- **`service_provider_code`** — Código do provedor de serviço. O valor padrão é `'171717'`.
- **`origin`** — Origem da aplicação, usada para validar as requisições. Por padrão ja foi defenido `'developer.mpesa.vm.co.mz'`
- **`initiatorIdentifier`** — Identificador do iniciador, que autoriza as transações.
- **`securityCredential`** — Credencial de segurança encriptada, utilizada para verificar a identidade do iniciador.

### Exemplo de configuração:

```php
use BrilliantMind\MPesa\Config\Config;

// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'development', // ou 'production'
    service_provider_code: '171717',
    origin: 'developer.mpesa.vm.co.mz',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);
```

### C2B
- A Chamada API C2B é utilizada como uma transação normal entre clientes e empresas. Os fundos da carteira de dinheiro móvel do cliente serão deduzidos e transferidos para a carteira de dinheiro móvel da empresa. Para autenticar e autorizar esta transação, a M-Pesa Payments Gateway iniciará uma mensagem USSD Push para o cliente para recolher e verificar o número PIN do dinheiro móvel. Este número não é armazenado e é utilizado apenas para autorizar a transação.

```php
<?php

require _DIR_.'/vendor/autoload.php';

use \BrilliantMind\MPesa\Mpesa;


// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'environment', // development ou 'production'
    service_provider_code: 'service-provider',
    origin: 'origin',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);

$transactionReference = bin2hex(random_bytes(6)); 
$thirdPartyReference = bin2hex(random_bytes(6));
$response = Mpesa::c2b(1, '258846568447', $transactionReference, $thirdPartyReference);

echo '<pre>';
print_r($response->toArray());
```

### B2C
- A Chamada API B2C é utilizada como uma transação normal entre empresas e clientes. Os fundos da carteira de dinheiro móvel da empresa serão deduzidos e transferidos para a carteira de dinheiro móvel do cliente terceiro.

```php
<?php

require _DIR_.'/vendor/autoload.php';

use \BrilliantMind\MPesa\Mpesa;

// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'environment', // development ou 'production'
    service_provider_code: 'service-provider',
    origin: 'origin',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);

$transactionReference = bin2hex(random_bytes(6)); 
$thirdPartyReference = bin2hex(random_bytes(6));
$response = Mpesa::b2c(1, '258846568447', $transactionReference, $thirdPartyReference);

echo '<pre>';
print_r($response->toArray());
```

### B2B
- A Chamada API B2B é utilizada como uma transação normal entre empresas. Os fundos da carteira de dinheiro móvel da empresa serão deduzidos e transferidos para a carteira de dinheiro móvel da empresa terceira.

```php
<?php

require _DIR_.'/vendor/autoload.php';

use \BrilliantMind\MPesa\Mpesa;

// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'environment', // development ou 'production'
    service_provider_code: 'service-provider',
    origin: 'origin',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);

$transactionReference = bin2hex(random_bytes(6)); 
$thirdPartyReference = bin2hex(random_bytes(6));
$response = Mpesa::b2b(1, '258846568447', $transactionReference, $thirdPartyReference);

echo '<pre>';
print_r($response->toArray());
```

### Transaction
- A API Consultar estado da transação é utilizada para determinar o estado atual de uma determinada transação. Utilizando a ID da transação ou a ID da conversação da transação da plataforma de dinheiro móvel, o gateway de pagamentos M-Pesa devolverá informações sobre o estado da transação.

```php
<?php

require _DIR_.'/vendor/autoload.php';

use \BrilliantMind\MPesa\Mpesa;

// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'environment', // development ou 'production'
    service_provider_code: 'service-provider',
    origin: 'origin',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);

$transactionReference = bin2hex(random_bytes(6)); 
$thirdPartyReference = bin2hex(random_bytes(6));
$response = Mpesa::transaction($transactionReference, $thirdPartyReference);

echo '<pre>';
print_r($response->toArray());
```

### Reversal
- A API de reversão é utilizada para reverter uma transação bem sucedida. Utilizando o ID da transação de uma transação anterior bem sucedida, o Portal de Pagamentos M-Pesa retira os fundos da carteira de dinheiro móvel do destinatário e reverte os fundos para a carteira de dinheiro móvel da parte que iniciou a transação original.
```php
<?php

require _DIR_.'/vendor/autoload.php';

use \BrilliantMind\MPesa\Mpesa;

// Configuração da API M-Pesa
Mpesa::config(
    api_key: 'your-api-key',
    public_key: 'your-public-key',
    environment: 'environment', // development ou 'production'
    service_provider_code: 'service-provider',
    origin: 'origin',
    initiatorIdentifier: 'your-initiator-id',
    securityCredential: 'your-security-credential'
);

$transactionReference = bin2hex(random_bytes(6)); 
$thirdPartyReference = bin2hex(random_bytes(6));
$response = Mpesa::reversal(1, $transactionReference, $thirdPartyReference);

echo '<pre>';
print_r($response->toArray());
```


## Requisitos
- **`PHP ^8.1`**
