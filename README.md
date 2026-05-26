# M-Pesa PHP SDK (Laravel)

SDK para interagir com a API M-Pesa Moçambique: C2B, B2B, B2C, consulta de transação e reversão.

## Instalação

```shell
composer require brilliant_mind/m-pesa
```

O pacote usa _package auto-discovery_ — o `ServiceProvider` e o alias `Mpesa` são registados automaticamente.

## Configuração

### 1. Variáveis de ambiente (`.env`)

#### Desenvolvimento (sandbox)

```dotenv
MPESA_ENV=development
MPESA_API_KEY=sua_api_key_sandbox
MPESA_PUBLIC_KEY=sua_public_key_sandbox
MPESA_SERVICE_PROVIDER_CODE=171717
MPESA_INITIATOR_IDENTIFIER=
MPESA_SECURITY_CREDENTIAL=
```

#### Produção

```dotenv
MPESA_ENV=production
MPESA_API_KEY=sua_api_key_producao
MPESA_PUBLIC_KEY=sua_public_key_producao
MPESA_SERVICE_PROVIDER_CODE=seu_codigo
MPESA_INITIATOR_IDENTIFIER=seu_initiator
MPESA_SECURITY_CREDENTIAL=seu_credential_encriptado
```

### 2. (Opcional) Publicar o ficheiro de configuração

Só é necessário se quiser alterar valores além do `.env`:

```shell
php artisan vendor:publish --tag=mpesa-config
```

Isto cria `config/mpesa.php`.

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

### Reversão

```php
$response = Mpesa::reversal($amount, $transactionID, $thirdPartyReference);
```

## Requisitos

- PHP `^8.1`
- Laravel 10, 11 ou 12
- Extensões: `openssl`, `json`, `curl`
