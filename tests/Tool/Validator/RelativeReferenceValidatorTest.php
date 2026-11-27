<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Tool\Validator;

use JsonSchema\Tool\Validator\RelativeReferenceValidator;
use PHPUnit\Framework\TestCase;

class RelativeReferenceValidatorTest extends TestCase
{
    /** @dataProvider validRelativeReferenceDataProvider */
    public function testValidRelativeReferencesAreValidatedAsSuch(string $ref): void
    {
        self::assertTrue(RelativeReferenceValidator::isValid($ref));
    }

    /** @dataProvider invalidRelativeReferenceDataProvider */
    public function testInvalidRelativeReferencesAreValidatedAsSuch(string $ref): void
    {
        self::assertFalse(RelativeReferenceValidator::isValid($ref));
    }

    public function validRelativeReferenceDataProvider(): \Generator
    {
        yield 'Relative path from root' => ['ref' => '/relative/path'];
        yield 'Relative path up one level' => ['ref' => '../up-one-level'];
        yield 'Relative path from current' => ['ref' => 'foo/bar'];
        yield 'Empty fragment (RFC 3986: path-empty + fragment)' => ['ref' => '#'];
        yield 'Fragment only' => ['ref' => '#section'];
        yield 'Empty query (RFC 3986: path-empty + query)' => ['ref' => '?'];
        yield 'Empty query and empty fragment' => ['ref' => '?#'];
        yield 'Empty query and fragment' => ['ref' => '?#fragment'];
        yield 'Query and fragment' => ['ref' => '?query#fragment'];
        yield 'Percent-encoded triplet' => ['ref' => '/%41'];
        yield 'Percent-encoded triplet in lower case' => ['ref' => '/%c3%a9'];
        yield 'Network-path reference with an empty authority' => ['ref' => '//'];
        yield 'Colon in a segment after a dot-segment' => ['ref' => './a:b'];
    }

    public function invalidRelativeReferenceDataProvider(): \Generator
    {
        yield 'Absolute URI' => ['ref' => 'http://example.com'];
        yield 'Three slashes' => ['ref' => '///three/slashes'];
        yield 'Path with spaces' => ['ref' => '/path with spaces'];
        yield 'Lone percent sign' => ['ref' => '/%'];
        yield 'Incomplete percent-encoding triplet' => ['ref' => '/%A'];
        yield 'Percent-encoding with non-hex digits' => ['ref' => '/%zz'];
        yield 'Percent sign in a query' => ['ref' => '?100%'];
        yield 'Non-ASCII character' => ['ref' => '/foobar®.txt'];
        yield 'Double quote in a path' => ['ref' => '/a"b'];
        yield 'Square brackets outside an authority' => ['ref' => '/[::1]'];
        yield 'Non-numeric port' => ['ref' => '//example.com:abc/p'];
        yield 'More than one at-sign in the authority' => ['ref' => '//a@b@example.com/'];
        yield 'Leading zero in the IPv4 part of an IPv6 literal' => ['ref' => '//[::ffff:192.168.0.01]/p'];
        yield 'Colon in the first segment of a relative-path reference' => ['ref' => '1:b'];
        yield 'Trailing new line' => ['ref' => "/foo\n"];
    }
}
