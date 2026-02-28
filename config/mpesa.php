<?php

return [
    'api_key' => env('MPESA_API_KEY', ''),
    'public_key' => env('MPESA_PUBLIC_KEY', ''),
    'environment' => env('MPESA_ENVIRONMENT', 'development'), // development|production
    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE', '171717'),
    'origin' => env('MPESA_ORIGIN', 'developer.mpesa.vm.co.mz'),
    'initiatorIdentifier' => env('MPESA_INITIATOR_IDENTIFIER', ''),
    'securityCredential' => env('MPESA_SECURITY_CREDENTIAL', ''),
];