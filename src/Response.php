<?php

namespace BrilliantMind\MPesa;

abstract class Response
{
    public static array $codes = [
        'INS-0'    => ['code' => 200, 'message' => 'Request processed successfully'],
        'INS-1'    => ['code' => 500, 'message' => 'Internal Error'],
        'INS-2'    => ['code' => 401, 'message' => 'Invalid API Key'],
        'INS-4'    => ['code' => 400, 'message' => 'User is not active'],
        'INS-5'    => ['code' => 400, 'message' => 'Transaction cancelled by customer'],
        'INS-6'    => ['code' => 400, 'message' => 'Transaction Failed'],
        'INS-9'    => ['code' => 408, 'message' => 'Request timeout'],
        'INS-10'   => ['code' => 400, 'message' => 'Duplicate Transaction'],
        'INS-13'   => ['code' => 400, 'message' => 'Invalid Shortcode Used'],
        'INS-14'   => ['code' => 400, 'message' => 'Invalid Reference Used'],
        'INS-15'   => ['code' => 400, 'message' => 'Invalid Amount Used'],
        'INS-16'   => ['code' => 500, 'message' => 'Unable to handle the request due to a temporary overloading'],
        'INS-17'   => ['code' => 400, 'message' => 'Invalid Transaction Reference. Length Should Be Between 1 and 20.'],
        'INS-18'   => ['code' => 400, 'message' => 'Invalid TransactionID Used'],
        'INS-19'   => ['code' => 400, 'message' => 'Invalid ThirdPartyReference Used'],
        'INS-20'   => ['code' => 400, 'message' => 'Not All Parameters Provided. Please try again.'],
        'INS-21'   => ['code' => 400, 'message' => 'Parameter validations failed. Please try again.'],
        'INS-22'   => ['code' => 400, 'message' => 'Invalid Operation Type'],
        'INS-23'   => ['code' => 400, 'message' => 'Unknown Status. Contact Customer Services for assistance.'],
        'INS-24'   => ['code' => 400, 'message' => 'Invalid InitiatorIdentifier Used'],
        'INS-25'   => ['code' => 400, 'message' => 'Invalid SecurityCredential Used'],
        'INS-26'   => ['code' => 401, 'message' => 'Not authorised'],
        'INS-993'  => ['code' => 400, 'message' => 'Direct Debit Missing'],
        'INS-994'  => ['code' => 400, 'message' => 'Direct Debit Already Exists'],
        'INS-995'  => ['code' => 400, 'message' => "Customer's profile has problems"],
        'INS-996'  => ['code' => 400, 'message' => 'Customer Account Status Not Active'],
        'INS-997'  => ['code' => 400, 'message' => 'Linking Transaction Not Found'],
        'INS-998'  => ['code' => 400, 'message' => 'Invalid Market'],
        'INS-2001' => ['code' => 401, 'message' => 'Initiator authentication error'],
        'INS-2002' => ['code' => 400, 'message' => 'Receiver invalid'],
        'INS-2006' => ['code' => 400, 'message' => 'Insufficient balance'],
        'INS-2051' => ['code' => 400, 'message' => 'Invalid MSISDN'],
        'INS-2057' => ['code' => 400, 'message' => 'MSISDN is not registered'],
    ];

    public static function describe(?string $code): array
    {
        return self::$codes[$code] ?? ['code' => 0, 'message' => 'Unknown response code'];
    }
}
