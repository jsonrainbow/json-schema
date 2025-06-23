<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

/**
 * Schemas in these tests look like draft-03, but the 'disallow' patterns provided are in
 * violation of the spec - 'disallow' as defined in draft-03 accepts the same values as the
 * 'type' option, and cannot take arbitrary patterns. The implementation in this library is
 * probably deliberate, but noting that it's invalid, schema validation has been disabled
 * for these tests. The 'disallow' option was removed permanently in draft-04.
 */
class DisallowTest extends BaseTestCase
{
    /** @var string */
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":"The xpto is weird"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"any",
                  "disallow":{"type":"string","pattern":"xpto"}
                }
              }
            }'
        ];
        yield [
            '{
              "value":null
            }',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"any",
                  "disallow":{"type":"null"}
                }
              }
            }'
        ];
        yield [
            '{"value": 1}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "any", "disallow": "integer"}
                }
            }'
        ];
        yield [
            '{"value": true}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "any", "disallow": ["integer", "boolean"]}
                }
            }'
        ];
        yield [
            '{"value": "foo"}',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "any",
                        "disallow":
                            ["string", {
                                "type": "object",
                                "properties": {
                                    "foo": {"type": "string"}
                                }
                            }]
                    }
                }
            }'
        ];
        yield [
            '{"value": {"foo": "bar"}}',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "any",
                        "disallow":
                            ["string", {
                                "type": "object",
                                "properties": {
                                    "foo": {"type": "string"}
                                }
                            }]
                    }
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "value":"The xpto is weird"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"any",
                  "disallow":{"type":"string","pattern":"^xpto"}
                }
              }
            }'
        ];
        yield [
            '{
              "value":1
            }',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"any",
                  "disallow":{"type":"null"}
                }
              }
            }'
        ];
        yield [
            '{"value": {"foo": 1}}',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "any",
                        "disallow":
                            ["string", {
                                "type": "object",
                                "properties": {
                                    "foo": {"type": "string"}
                                }
                            }]
                    }
                }
            }'
        ];
        yield [
            '{"value": true}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "any", "disallow": "string"}
                }
            }'
        ];
    }
}
