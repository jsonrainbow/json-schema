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
    }

    public function invalidRelativeReferenceDataProvider(): \Generator
    {
        yield 'Absolute URI' => ['ref' => 'http://example.com'];
        yield 'Three slashes' => ['ref' => '///three/slashes'];
        yield 'Path with spaces' => ['ref' => '/path with spaces'];
    }
}
