<?php

namespace BrilliantMind\MPesa;


abstract class Response
{
    public static array $codes = [
        'INS-0' => [
            'code' => 200,
            'message' => 'Request processed successfully'
        ],
        'INS-2' => [
            'code' => 500,
            'message' => 'Internal Error'
        ]
    ];
}