<?php

namespace JsonSchema\Tests\Constraints;

class ConstTest extends BaseTestCase
{
    /** @var string  */
    protected $schemaSpec = 'http://json-schema.org/draft-06/schema#';
    /** @var bool  */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield 'Object with inner string value' => [
            '{"value":"foo"}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","const":"bar"}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Object with inner integer value' => [
            '{"value":5}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","const":6}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Object with inner boolean value' => [
            '{"value":false}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"boolean","const":true}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Object with inner numerical string value' => [
                '{
                    "value": {
                        "foo": "12"
                    }
                }',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any", 
                            "const": {
                                "foo": 12
                            }
                        }
                    }
                }'
            ];
    }

    public function getValidTests(): \Generator
    {
        yield 'String value' => [
            '{"value":"bar"}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","const":"bar"}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Boolean(false) value' => [
            '{"value":false}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"boolean","const":false}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Boolean(true) value' => [
            '{"value":true}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"boolean","const":true}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Integer value' => [
            '{"value":5}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","const":5}
              },
              "additionalProperties":false
            }'
        ];
        yield 'Object with inner integer value' => [
                '{
                    "value": {
                        "foo": 12
                    }
                }',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "object",
                            "const": {
                                    "foo": 12
                            }
                        }
                    }
                }'
            ];
    }
}
