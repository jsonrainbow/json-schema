<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool\Validator;

use JsonSchema\Tool\Validator\EmailValidator;
use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    /** @dataProvider validEmailDataProvider */
    public function testValidEmailsAreValidatedAsSuch(string $email): void
    {
        self::assertTrue(EmailValidator::isValid($email));
    }

    /** @dataProvider invalidEmailDataProvider */
    public function testInvalidEmailsAreValidatedAsSuch(string $email): void
    {
        self::assertFalse(EmailValidator::isValid($email));
    }

    public function validEmailDataProvider(): \Generator
    {
        yield 'Simple address' => ['email' => 'joe.bloggs@example.com'];
        yield 'Quoted string with a space' => ['email' => '"joe bloggs"@example.com'];
        yield 'Quoted string with only a space' => ['email' => '" "@iana.org'];
        yield 'Quoted pair' => ['email' => '"\a"@iana.org'];
        yield 'Single-label domain' => ['email' => 'test@io'];
        yield 'Domain that looks like an IPv4 address' => ['email' => 'test@255.255.255.255'];
        yield 'IPv4 address literal' => ['email' => 'joe.bloggs@[127.0.0.1]'];
        yield 'IPv6 address literal' => ['email' => 'joe.bloggs@[IPv6:::1]'];
        yield 'IPv6 address literal with a lowercase tag' => ['email' => 'a@[ipv6:::1]'];
    }

    public function invalidEmailDataProvider(): \Generator
    {
        yield 'Non-ASCII character in the local part' => ['email' => 'aé@iana.org'];
        yield 'Non-ASCII character in the domain' => ['email' => 'a@é.org'];
        yield 'Two subsequent dots in the local part' => ['email' => 'te..st@example.com'];
        yield 'Unquoted space in the local part' => ['email' => 'joe bloggs@example.com'];
        yield 'Trailing new line' => ['email' => "test@iana.org\n"];
        yield 'Domain ending with a dot' => ['email' => 'test@iana.org.'];
        yield 'Domain label starting with a hyphen' => ['email' => 'test@-iana.org'];
        yield 'Invalid IPv4 address literal' => ['email' => 'joe.bloggs@[127.0.0.300]'];
        yield 'Address literal that is not an address' => ['email' => 'test@[RFC-5322-domain-literal]'];
        yield 'Local part exceeding 64 octets' => ['email' => str_repeat('a', 65) . '@example.com'];
        yield 'Missing local part' => ['email' => '@example.com'];
    }
}
