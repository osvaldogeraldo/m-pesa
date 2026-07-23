<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Both values come from the M-Pesa developer portal.
    | Wrap the public key in double quotes inside .env; it is a long base64 string.
    | Alternatively leave MPESA_PUBLIC_KEY empty and point MPESA_PUBLIC_KEY_PATH
    | to a file containing the key (handy for very long values).
    |
    */

    'api_key' => env('MPESA_API_KEY', ''),

    'public_key' => env('MPESA_PUBLIC_KEY', ''),

    'public_key_path' => env('MPESA_PUBLIC_KEY_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | "development" talks to api.sandbox.vm.co.mz, "production" to api.vm.co.mz.
    |
    */

    'environment' => env('MPESA_ENVIRONMENT', 'development'),

    /*
    |--------------------------------------------------------------------------
    | Merchant details
    |--------------------------------------------------------------------------
    |
    | 171717 is the shortcode used by the sandbox. Replace it with your own
    | shortcode before going live.
    |
    */

    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE', '171717'),

    'origin' => env('MPESA_ORIGIN', 'developer.mpesa.vm.co.mz'),

    /*
    |--------------------------------------------------------------------------
    | Reversal credentials
    |--------------------------------------------------------------------------
    |
    | Only required by the reversal endpoint.
    |
    */

    'initiatorIdentifier' => env('MPESA_INITIATOR_IDENTIFIER', ''),

    'securityCredential' => env('MPESA_SECURITY_CREDENTIAL', ''),

    /*
    |--------------------------------------------------------------------------
    | Connection
    |--------------------------------------------------------------------------
    |
    | Leave "host" and "port" empty to use the defaults resolved from the
    | environment. The gateway listens on a different port per operation; set
    | MPESA_PORT only if you need to force a single port for all of them.
    |
    */

    'host' => env('MPESA_HOST', ''),

    'port' => env('MPESA_PORT', ''),

    'ports' => [
        'c2b' => env('MPESA_PORT_C2B', '18352'),
        'b2b' => env('MPESA_PORT_B2B', '18349'),
        'b2c' => env('MPESA_PORT_B2C', '18345'),
        'status' => env('MPESA_PORT_STATUS', '18353'),
        'reversal' => env('MPESA_PORT_REVERSAL', '18354'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    |
    | C2B pushes a USSD prompt to the customer and waits for the PIN, so the
    | gateway can legitimately take up to ~90 seconds to answer.
    |
    */

    'timeout' => (float) env('MPESA_TIMEOUT', 90),

    'connect_timeout' => (float) env('MPESA_CONNECT_TIMEOUT', 30),

    'verify_ssl' => (bool) env('MPESA_VERIFY_SSL', true),

];
