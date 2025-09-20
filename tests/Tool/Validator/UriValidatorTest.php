<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool\Validator;

use JsonSchema\Tool\Validator\UriValidator;
use PHPUnit\Framework\TestCase;

class UriValidatorTest extends TestCase
{
    /** @dataProvider validUriDataProvider */
    public function testValidUrisAreValidatedAsSuch(string $uri): void
    {
        self::assertTrue(UriValidator::isValid($uri));
    }

    /** @dataProvider invalidUriDataProvider */
    public function testInvalidUrisAreValidatedAsSuch(string $uri): void
    {
        self::assertFalse(UriValidator::isValid($uri));
    }

    public function validUriDataProvider(): \Generator
    {
        yield 'Simple HTTP URI' => ['uri' => 'https://example.com'];
        yield 'Subdomain HTTP URI' => ['uri' => 'https://sub.domain.example.com'];
        yield 'Full HTTP URI' => ['uri' => 'https://example.com:8080/path/to/resource?query=string#fragment'];
        yield 'Full FTP URI' => ['uri' => 'ftp://user:pass@ftp.example.com:21/path'];
        yield 'IPV6 HTTP URI' => ['uri' => 'http://[2001:db8::ff00:42:8329]'];
        yield 'Mailto URI' => ['uri' => 'mailto:user@example.com'];
        yield 'Data URI' => ['uri' => 'data:text/plain;charset=utf-8,Hello%20World!'];
        yield 'ISBN URN URI' => ['uri' => 'urn:isbn:0451450523'];
        yield 'OASIS URN URI' => ['uri' => 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2'];
    }

    public function invalidUriDataProvider(): \Generator
    {
        yield 'Invalid schema' => ['uri' => 'ht!tp://example.com'];
        yield 'Missing schema' => ['uri' => '://example.com'];
        yield 'Double dot in domain' => ['uri' => 'https://example..com'];
        yield 'To high of a port number' => ['uri' => 'https://example.com:65536'];
        yield 'Invalid path characters with "<>"' => ['uri' => 'http://example.com/<>'];
        yield 'Invalid path characters with "{}"' => ['uri' => 'http://example.com/{bad}'];
        yield 'Invalid path characters with "^"' => ['uri' => 'http://example.com/^invalid'];
        yield 'Only mailto:' => ['uri' => 'mailto:'];
        yield 'Invalid email used in mailto:' => ['uri' => 'mailto:user@.com'];
    }
}
