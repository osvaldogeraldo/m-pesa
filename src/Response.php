<?php

namespace BrilliantMind\MPesa;

abstract class Response
{
    /**
     * Response code returned by M-Pesa when everything went through.
     */
    public const SUCCESS = 'INS-0';

    /**
     * Every documented M-Pesa response code, mapped to its HTTP equivalent and message.
     *
     * @var array<string, array{code: int, message: string}>
     */
    public static array $codes = [
        'INS-0' => ['code' => 200, 'message' => 'Request processed successfully'],
        'INS-1' => ['code' => 500, 'message' => 'Internal Error'],
        'INS-2' => ['code' => 401, 'message' => 'Invalid API Key'],
        'INS-4' => ['code' => 422, 'message' => 'User is not active'],
        'INS-5' => ['code' => 422, 'message' => 'Transaction cancelled by customer'],
        'INS-6' => ['code' => 422, 'message' => 'Transaction Failed'],
        'INS-9' => ['code' => 408, 'message' => 'Request timeout'],
        'INS-10' => ['code' => 409, 'message' => 'Duplicate Transaction'],
        'INS-13' => ['code' => 422, 'message' => 'Invalid Shortcode Used'],
        'INS-14' => ['code' => 422, 'message' => 'Invalid Reference Used'],
        'INS-15' => ['code' => 422, 'message' => 'Invalid Amount Used'],
        'INS-16' => ['code' => 503, 'message' => 'Unable to handle the request due to a temporary overloading'],
        'INS-17' => ['code' => 422, 'message' => 'Invalid Transaction Reference. Length Should Be Between 1 and 20'],
        'INS-18' => ['code' => 422, 'message' => 'Invalid TransactionID Used'],
        'INS-19' => ['code' => 422, 'message' => 'Invalid ThirdPartyReference Used'],
        'INS-20' => ['code' => 422, 'message' => 'Not All Parameters Provided. Please try again'],
        'INS-21' => ['code' => 422, 'message' => 'Parameter validations failed. Please try again'],
        'INS-22' => ['code' => 422, 'message' => 'Invalid Operation Type'],
        'INS-23' => ['code' => 500, 'message' => 'Unknown Status. Contact M-Pesa Support'],
        'INS-24' => ['code' => 422, 'message' => 'Invalid InitiatorIdentifier Used'],
        'INS-25' => ['code' => 422, 'message' => 'Invalid SecurityCredential Used'],
        'INS-26' => ['code' => 403, 'message' => 'Not authorized'],
        'INS-993' => ['code' => 422, 'message' => 'Direct Debit Missing'],
        'INS-994' => ['code' => 409, 'message' => 'Direct Debit Already Exists'],
        'INS-995' => ['code' => 422, 'message' => "Customer's Profile Has Problems"],
        'INS-996' => ['code' => 422, 'message' => 'Customer Account Status Not Active'],
        'INS-997' => ['code' => 404, 'message' => 'Linked Transaction Not Found'],
        'INS-998' => ['code' => 422, 'message' => 'Invalid Market'],
        'INS-2001' => ['code' => 401, 'message' => 'Initiator authentication error'],
        'INS-2002' => ['code' => 422, 'message' => 'Receiver invalid'],
        'INS-2006' => ['code' => 422, 'message' => 'Insufficient balance'],
        'INS-2051' => ['code' => 422, 'message' => 'MSISDN invalid'],
        'INS-2057' => ['code' => 422, 'message' => 'Language code invalid'],
    ];

    /**
     * Full entry for a response code, with a safe fallback for unknown ones.
     *
     * @return array{code: int, message: string}
     */
    public static function describe(?string $responseCode): array
    {
        if ($responseCode === null || $responseCode === '') {
            return ['code' => 0, 'message' => 'Unknown response from the M-Pesa gateway'];
        }

        return self::$codes[$responseCode] ?? ['code' => 0, 'message' => "Unknown response code ({$responseCode})"];
    }

    /**
     * Human readable description for a response code.
     */
    public static function message(?string $responseCode): string
    {
        if ($responseCode === null || $responseCode === '') {
            return 'Unknown response from the M-Pesa gateway';
        }

        return self::$codes[$responseCode]['message'] ?? "Unknown response code ({$responseCode})";
    }

    /**
     * HTTP status that best represents a response code.
     */
    public static function status(?string $responseCode): int
    {
        if ($responseCode === null || $responseCode === '') {
            return 500;
        }

        return self::$codes[$responseCode]['code'] ?? 500;
    }

    public static function isSuccessful(?string $responseCode): bool
    {
        return $responseCode === self::SUCCESS;
    }
}
