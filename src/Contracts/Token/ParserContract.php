<?php

namespace BrilliantMind\MPesa\Contracts\Token;

interface ParserContract
{
    /**
     * Parse public and private key into token.
     *
     * @param string $publicKey
     * @param string $privateKey
     * @return string
     */
    public static function parse(string $publicKey, string $privateKey): string;
}