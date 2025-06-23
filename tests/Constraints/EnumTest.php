<?php

namespace JsonSchema\Tests\Constraints;

class EnumTest extends BaseTestCase
{
    /** @var string */
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":"Morango"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"string",
                  "enum":["Abacate","Manga","Pitanga"],
                  "required":true
                }
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{"value": "4"}',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "integer", "enum": [1, 2, 3]
                    }
                },
                "additionalProperties": false
            }'
        ];
        yield [
            '{"value": {"foo": false}}',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "any", "enum": [6, "foo", [], true, {"foo": 12}]
                    }
                },
                "additionalProperties": false
            }'
        ];
        yield [
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
                        "enum": [
                            6, 
                            "foo", 
                            [], 
                            true, 
                            {
                                "foo": 12
                            }
                        ]
                    }
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "value":"Abacate"
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"string","enum":["Abacate","Manga","Pitanga"]}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "value":{
                  "type":"string",
                  "enum":["Abacate","Manga","Pitanga"],
                  "required":false
                }
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{"value": 1}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "enum": [1, 2, 3]}
                }
            }'
        ];
        yield [
            '{"value": []}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "any", "enum": [6, "foo", [], true, {"foo": 12}]}
                },
                "additionalProperties": false
            }'
        ];
        yield [
            '{
                "value": {
                    "foo": 12
                }
            }',
            '{
                "type": "object",
                "properties": {
                    "value": {
                        "type": "any",
                        "enum": [
                            6,
                            "foo",
                            [],
                            true,
                            {
                                "foo": 12
                            }
                        ]
                    }
                }
            }'
        ];
        yield 'Number values with mathematical equality are considered valid' => [
            'data' => '12',
            'schema' => '{
                "type": "any",
                "enum": [
                    12.0
                ]
            }'
        ];
        yield 'Array with number values with mathematical equality are considered valid' => [
            'input' => '[ 0.0 ]',
            'schema' => '{
                "enum": [
                    [ 0 ]
                ]
            }',
        ];
    }
}
