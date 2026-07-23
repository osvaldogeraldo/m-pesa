<?php

namespace BrilliantMind\MPesa\Tests\Unit;

use BrilliantMind\MPesa\Exceptions\EncryptionException;
use BrilliantMind\MPesa\Helpers\Parser;
use BrilliantMind\MPesa\Tests\TestCase;

class ParserTest extends TestCase
{
    public function test_it_builds_a_token_from_a_single_line_key(): void
    {
        $token = Parser::parse('my-api-key', self::samplePublicKey());

        $this->assertNotSame('', $token);
        $this->assertNotFalse(base64_decode($token, true));
    }

    public function test_it_accepts_a_full_pem_block(): void
    {
        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . wordwrap(self::samplePublicKey(), 64, "\n", true)
            . "\n-----END PUBLIC KEY-----\n";

        $this->assertNotSame('', Parser::parse('my-api-key', $pem));
    }

    public function test_it_accepts_a_key_pasted_with_spaces_and_line_breaks(): void
    {
        $wrapped = wordwrap(self::samplePublicKey(), 40, "\r\n", true);

        $this->assertNotSame('', Parser::parse('my-api-key', $wrapped));
    }

    public function test_it_explains_what_went_wrong_for_an_invalid_key(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('MPESA_PUBLIC_KEY');

        Parser::parse('my-api-key', 'clearly-not-a-key');
    }
}
