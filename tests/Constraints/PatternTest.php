<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class PatternTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":"Abacates"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","pattern":"^cat"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{"value": "abc"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^a*$"}
                },
                "additionalProperties": false
            }'
        ];
        yield [
            '{"value": "Ã¼"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^ü$"}
                },
                "additionalProperties": false
            }'
        ];
        yield 'ECMA 262 \d does not match a non-ASCII digit' => [
            '{"value": "৪২"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^\\\\d+$"}
                }
            }'
        ];
        yield 'ECMA 262 \w does not match a non-ASCII letter' => [
            '{"value": "é"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^\\\\w$"}
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "value":"Abacates"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","pattern":"tes$"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "value":"Abacates"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","pattern":"cat"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{"value": "aaa"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^a*$"}
                },
                "additionalProperties": false
            }'
        ];
        yield [
            '{"value": "↓æ→"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^↓æ.$"}
                },
                "additionalProperties": false
            }'
        ];
        yield 'ECMA 262 \d matches an ASCII digit' => [
            '{"value": "42"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^\\\\d+$"}
                }
            }'
        ];
        yield 'A pattern with a slash' => [
            '{"value": "a/b"}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "string", "pattern": "^a/b$"}
                }
            }'
        ];
    }
}
