<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Draft06;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Validator;

/**
 * ECMA-262 defines \d, \D, \w and \W over ASCII only, while PCRE makes them Unicode
 * aware under the /u modifier. Patterns must keep their ECMA meaning.
 */
class EcmaPatternTest extends VeryBaseTestCase
{
    /**
     * @dataProvider provideNonAsciiCases
     */
    public function testShorthandClassesKeepTheirAsciiOnlyEcmaMeaning(string $schema, string $data, bool $expectedToBeValid): void
    {
        $validator = new Validator();
        $decodedData = json_decode($data);
        $validator->validate($decodedData, json_decode($schema), Constraint::CHECK_MODE_STRICT);

        self::assertSame($expectedToBeValid, $validator->isValid(), (string) json_encode($validator->getErrors()));
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function provideNonAsciiCases(): array
    {
        $dialect = '"$schema": "http://json-schema.org/draft-06/schema#"';

        return [
            'ASCII digit matches \\d in patternProperties' => [
                '{' . $dialect . ', "patternProperties": {"^\\\\d+$": {"type": "string"}}, "additionalProperties": false}',
                '{"5": "ok"}',
                true,
            ],
            'Arabic-Indic digit does not match \\d in patternProperties' => [
                '{' . $dialect . ', "patternProperties": {"^\\\\d+$": {"type": "string"}}, "additionalProperties": false}',
                '{"٣": "ok"}',
                false,
            ],
            'accented letter does not match \\w in pattern' => [
                '{' . $dialect . ', "type": "string", "pattern": "^\\\\w+$"}',
                '"é"',
                false,
            ],
        ];
    }
}
