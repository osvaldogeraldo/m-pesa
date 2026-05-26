<?php

return [
    'api_key'               => env('MPESA_API_KEY', ''),
    'public_key'            => env('MPESA_PUBLIC_KEY', ''),
    'environment'           => env('MPESA_ENVIRONMENT', env('MPESA_ENV', 'development')), // development|production
    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE', '171717'),
    'host'                  => env('MPESA_HOST', ''), // opcional — override do host (por defeito usa api.sandbox.vm.co.mz ou api.vm.co.mz)
    'origin'                => env('MPESA_ORIGIN', 'developer.mpesa.vm.co.mz'),
    'initiator_identifier'  => env('MPESA_INITIATOR_IDENTIFIER', ''),
    'security_credential'   => env('MPESA_SECURITY_CREDENTIAL', ''),
];
